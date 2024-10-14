<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>{{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
</head>

<body style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;">
    <div
        style="background-color: rgb(37 99 235); max-width: 32rem; padding: 4rem 1rem; margin-left: auto; margin-right: auto; color: rgb(255 255 255);">
        <div style="text-align: center; font-size: 1.5rem; line-height: 2rem; font-weight: 600;">
            <h1>Your order is confirmed!</h1>
            <p>And we're just as excited as you are.</p>
        </div>
        <div
            style="border-radius: 0.5rem; background-color: rgb(244 244 245); color: rgb(24 24 27); padding-top: 1rem; padding-bottom: 1rem;">
            <h4
                style="color: rgb(37 99 235); font-size: 1.25rem; line-height: 1.75rem; font-weight: 700; text-align: center;">
                Here's what you ordered:
            </h4>
            <table
                style="border-collapse: collapse; border-bottom-width: 1px; border-color: rgb(161 161 170); width: 100%;">
                <tr>
                    <td style="text-align: center; padding: 1rem; width: 33.333333%; text-transform: uppercase;">
                        #{{ explode('-', $order->order_id)[0] }}
                    </td>
                    <td style="text-align: center; padding: 1rem; width: 33.333333%; text-transform: uppercase;">
                        {{ $order->created_at }}
                    </td>
                </tr>
            </table>
            <table
                style="border-collapse: collapse; border-bottom-width: 1px; border-color: rgb(161 161 170); width: 90%; margin-left: auto; margin-right: auto;">
                    <tr>
                        <th
                            style="text-align: left; font-weight: 600; text-transform: uppercase; color: rgb(113 113 122); font-size: 1rem; padding-top: 0.5rem; padding-bottom: 0.5rem;">
                            Item
                        </th>
                        <th
                            style="text-align: right; font-weight: 600; text-transform: uppercase; color: rgb(113 113 122); font-size: 1rem; padding-top: 0.5rem; padding-bottom: 0.5rem;">
                            Qty
                        </th>
                        <th
                            style="text-align: right; font-weight: 600; text-transform: uppercase; color: rgb(113 113 122); font-size: 1rem; padding-top: 0.5rem; padding-bottom: 0.5rem;">
                            Cost
                        </th>
                    </tr>
                    <tr>
                        <td style="text-align: left; font-size: 1rem; padding-top: 0.5rem; padding-bottom: 0.5rem;">
                            Case {{ $model->name }} - {{ $color->name }}
                        </td>
                        <td style="text-align: right; font-size: 1rem; padding-top: 0.5rem; padding-bottom: 0.5rem;">
                            {{ number_format($order->quantity) }}</td>
                        <td style="text-align: right; font-size: 1rem; padding-top: 0.5rem; padding-bottom: 0.5rem;">
                            ${{ number_format($order->amount) }}</td>
                    </tr>
                    <tr style="color: rgb(82 82 91);">
                        <td style="text-align: left; font-size: 1rem; padding-top: 0.5rem; padding-bottom: 0.5rem;">
                            <span style="margin-left: 1.5rem;">Base</span>
                        </td>
                        <td></td>
                        <td style="text-align: right; font-size: 1rem; padding-top: 0.5rem; padding-bottom: 0.5rem;">
                            ${{ number_format($configuration->amount) }}</td>
                    </tr>
                    <tr style="color: rgb(82 82 91);">
                        <td style="text-align: right; font-size: 1rem; padding-top: 0.5rem; padding-bottom: 0.5rem;">
                            <span style="margin-left: 1.5rem; text-transform: capitalize">Material:
                                {{ $configuration->material }}</span>
                        </td>
                        <td></td>
                        <td style="text-align: right; font-size: 1rem; padding-top: 0.5rem; padding-bottom: 0.5rem;">
                            ${{ number_format($configuration->amount_material) }}</td>
                    </tr>
                    <tr style="color: rgb(82 82 91);">
                        <td style="text-align: right; font-size: 1rem; padding-top: 0.5rem; padding-bottom: 0.5rem;">
                            <span style="margin-left: 1.5rem; text-transform: capitalize">Finish:
                                {{ $configuration->finish }}</span>
                        </td>
                        <td></td>
                        <td style="text-align: right; font-size: 1rem; padding-top: 0.5rem; padding-bottom: 0.5rem;">
                            ${{ number_format($configuration->amount_finish) }}</td>
                    </tr>
            </table>

            <table
                style=" border-collapse: collapse; width: 70%; margin-left: auto; margin-right: auto; margin-top: 1.5rem; padding-top: 0.5rem; padding-bottom: 0.5rem;">
                <tr>
                    <td style="text-align: right">Subtotal:</td>
                    <td style="text-align: right">${{ number_format($order->amount * $order->quantity) }}</td>
                </tr>
                <tr>
                    <td style="text-align: right">Shipping fee:</td>
                    <td style="text-align: right">${{ number_format($order->shipping_fee) }}</td>
                </tr>
                <tr>
                    <td style="text-align: right">Total:</td>
                    <td style="text-align: right">${{ number_format($order->total_amount) }}</td>
                </tr>
            </table>
        </div>
        <div style="text-align: center">
            <p>
                If you have any questions, please contact our support team at
                <a href="mailto:thanhtritran8@gmail.com" style="text-decoration: underline">thanhtritran8@gmail.com</a>
            </p>
            <p>&copy; 2024 CaseKMA. All rights reserved.</p>
        </div>
    </div>
    </div>
</body>

</html>
