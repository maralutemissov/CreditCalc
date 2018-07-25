<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

class CalcController extends Controller
{

    public $months = [
        1 => 'Январь',
        2 => 'Февраль',
        3 => 'Март',
        4 => 'Апрель',
        5 => 'Май',
        6 => 'Июнь',
        7 => 'Июль',
        8 => 'Август',
        9 => 'Сентябрь',
        10 => 'Октябрь',
        11 => 'Ноябрь',
        12 => 'Декабрь'
    ];

    public function calculate(Request $request)
    {
        if ($request->ajax()) {
            $response = [
                'result' => false
            ];

            $validator = Validator::make($request->except(['_token']), [
                'amount' => 'required|numeric|min:1000',
                'term' => 'required|numeric|min:1',
                'rate' => 'required|numeric|min:0.1'
            ], [
                'amount.required' => 'Введите сумму кредита',
                'amount.numeric' => 'Сумма кредита должна быть числом',
                'amount.min' => 'Минимальная сумма кредита 1 000 тг.',
                'term.required' => 'Введите срок кредита',
                'term.numeric' => 'Срок кредита должен быть числом',
                'term.min' => 'Минимальный срок кредита 1 месяц',
                'rate.required' => 'Введите процентную ставку',
                'rate.numeric' => 'Процентная ставка должна быть числом',
                'rate.min' => 'Минимальная процентная ставка 0.1%'
            ]);

            if ($validator->fails()) {
                $response['errors'] = $validator->errors();
            }
            else {
                $currency_text = [
                    1 => 'тг.',
                    2 => '$',
                    3 => '€'
                ];
                $payment_type = $request->input('payment_type'); // Вид платежа
                $currency = $currency_text[$request->input('currency')]; // Валюта
                $term = $request->input('term') * $request->input('term_type'); // Срок кредита в месяцах
                $month_rate = ($request->input('rate') / 100 / $request->input('rate_type')); // Процентная ставка
                $amount = $request->input('amount'); // Сумма кредита
                $round = 2; // Знаков после запятой
                $month = $request->input('month');
                $year = $request->input('year');
                $schedule = [];
                $response['result'] = true;

                if ($payment_type == 'ann') {
                    $k = ($month_rate * pow((1 + $month_rate), $term)) / ( pow((1 + $month_rate), $term) - 1);
                    $payment = round($k * $amount, $round);
                    $overpay = ($payment * $term) - $amount;
                    $debt = $amount;
            
                    for ($i = 1; $i <= $term; $i++) {
                        $percent_pay = round($debt * $month_rate, $round);
                        $credit_pay = round($payment - $percent_pay, $round);
                        $debt = $debt - $credit_pay;

                        if ($debt < 0) {
                            $debt = 0;
                        }

                        $item = [
                            'date' => $this->months[$month] . ', ' . $year,
                            'percent_pay' => number_format($percent_pay, $round, ',', ' '),
                            'credit_pay' => number_format($credit_pay, $round, ',', ' '),
                            'payment' => number_format($payment, $round, ',', ' '),
                            'debt' => number_format($debt, $round, ',', ' ')
                        ];

                        $schedule[] = $item;

                        if ($month++ >= 12) {
                            $month = 1; 
                            $year++ ;
                        }
                    }

                    $info['total_payment'] = number_format(($payment * $term), $round, ',', ' ');
                    $info['total_credit_pay'] = number_format($amount, $round, ',', ' ');
                    $info['overpay'] = number_format($overpay, $round, ',', ' ');

                    $schedule_table = $this->generateSchedule('ann', $schedule, $info);
                }
                else {
                    $credit_pay = $amount / $term;
                    $schedule = [];
                    $debt = $amount;
                    $total_payment = 0;
                    $overpay = 0;

                    for ($i = 1; $i <= $term; $i++) {
                        $percent_pay = $debt * $month_rate;
                        $overpay += $percent_pay;
                        $payment = $percent_pay + $credit_pay;
                        $debt -= $credit_pay;
                        $total_payment += $payment;

                        if ($debt < 0) {
                            $debt = 0;
                        }

                        $item = [
                            'date' => $this->months[$month] . ', ' . $year,
                            'credit_pay' => number_format($credit_pay, $round, ',', ' '),
                            'debt' => number_format($debt, $round, ',', ' '),
                            'percent_pay' => number_format($percent_pay, $round, ',', ' '),
                            'payment' => number_format($payment, $round, ',', ' ')
                        ];

                        $schedule[] = $item;
                    }

                    $info['total_payment'] = number_format($total_payment, $round, ',', ' ');
                    $info['total_credit_pay'] = number_format($amount, $round, ',', ' ');
                    $info['overpay'] = number_format($overpay, $round, ',', ' ');

                    $schedule_table = $this->generateSchedule('dif', $schedule, $info);
                }

                $response['schedule'] = $schedule_table;
            }

            return response()->json($response);
        }
    }

    public function generateSchedule($payment_type, $schedule, $info)
    {
        $table = '';

        if (count($schedule) > 0) {
            $table .= '<table>
                <tr>
                    <th>№</th>
                    <th>Дата платежа</th>
                    <th>Сумма платежа</th>
                    <th>Основной долг</th>
                    <th>Начисленные %</th>
                    <th>Остаток задолженности</th>
                </tr>';

            foreach ($schedule as $index => $item) {
                $table .= '<tr' . ((($index % 2) == 0) ? ' class="white-row"' : '') . '>
                    <td>' . ($index + 1) . '</td>
                    <td>' . $item['date'] . '</td>
                    <td>' . $item['payment'] . '</td>
                    <td>' . $item['credit_pay'] . '</td>
                    <td>' . $item['percent_pay'] . '</td>
                    <td>' . $item['debt'] . '</td>
                </tr>';
            }

            $table .= '<tr>
                <th colspan="2">Итого по кредиту</th>
                <th>' . $info['total_payment'] . '</th>
                <th>' . $info['total_credit_pay'] . '</th>
                <th>' . $info['overpay'] . '</th>
                <th></th>
            </tr>';

            $table .= '</table>';
        }

        return $table;
    }
    
    public function index()
    {
        $months = $this->months;

        return view('welcome', compact('months'));
    }

}
