<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Кредитный калькулятор</title>
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    </head>
    <body>
        <div class="content">
            <div class="form">
                <form method="POST">
                    <div class="form__title">
                        <p><strong>Параметры кредита</strong></p>
                        <p>Настройки калькулятора позволяют задавать дополнительные параметры кредита, но нужно учитывать, что в каждом банке есть свои особенности расчетов</p>
                    </div>
                    <div class="form__main">
                        <table>
                            <tr>
                                <td>Сумма кредита:</td>
                                <td>
                                    <input type="text" name="amount" value="" autocomplete="off">
                                </td>
                                <td>
                                    <select name="currency">
                                        <option value="1">тг.</option>
                                        <option value="2">$</option>
                                        <option value="3">€</option>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td>Срок кредита:</td>
                                <td>
                                    <input type="text" name="term" maxlength="2" value="" autocomplete="off">
                                </td>
                                <td>
                                    <select name="term_type">
                                        <option value="1">мес.</option>
                                        <option value="12">лет</option>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td>Процентная ставка:</td>
                                <td>
                                    <input type="text" name="rate" maxlength="5" value="" autocomplete="off">
                                </td>
                                <td>
                                    <select name="rate_type">
                                        <option value="12">% в год</option>
                                        <option value="1">% в месяц</option>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td>Вид платежа:</td>
                                <td>
                                    <select name="payment_type">
                                        <option value="ann">аннуитетный</option>
                                        <option value="dif">дифференцированный</option>
                                    </select>
                                </td>
                                <td></td>
                            </tr>

                            <tr>
                                <td>Начало выплат:</td>
                                <td>
                                    <select name="month" class="select_month">
                                        @foreach ($months as $number => $month)
                                            <option value="{{ $number }}" @if (date('n') == $number) selected="selected" @endif>{{ $month }}</option>
                                        @endforeach
                                    </select>

                                    <select name="year" class="select_year">
                                        @for ($y = 2006; $y <= date('Y') + 7; $y++)
                                            <option value="{{ $y }}" @if ($y == date('Y')) selected="selected" @endif>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </td>
                                <td></td>
                            </tr>

                            <tr>
                                <td></td>
                                <td>
                                    <button id="calculate-button" type="submit">Рассчитать</button>
                                </td>
                                <td></td>
                            </tr>
                        </table>
                    </div>
                </form>
            </div>

            <div class="warning"></div>

            <div class="payment-table"></div>
        </div>

        <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>