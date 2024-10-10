<?php

namespace App\Http\Controllers;

use App\Enum\OrderStatus;
use App\Models\Configuration;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ShippingAddress;
use App\ThirdParty\Address;
use App\ThirdParty\Currency\Currency;
use App\ThirdParty\Paypal\Paypal;
use App\ThirdParty\Vnpay\Vnpay;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user('sanctum');

        $data = $request->all();
        if (isset($data['address'])) {
            $data['address']['country'] = strtoupper($data['address']['country'] ?? '');
        }
        $validated = $request->validate([
            'configuration_id' => [
                'required',
                function ($attribute, $value, $fail) use ($user) {
                    if (!$user->configurations()->find($value)) {
                        $fail('The configuration is invalid.');
                    }
                }
            ],
            'quantity' => 'required|integer|min:1',
            'shipping_address_id' => [
                'required_without:address',
                function ($attribute, $value, $fail) use ($user) {
                    if (!$user->shippingAddresses()->find($value)) {
                        $fail('The shipping addresses is invalid.');
                    }
                },
            ],
            'address' => [
                'required_without:shipping_address_id',
                'array'
            ],
            'address.name' => 'exclude_without:address|required|string',
            'address.phone_number' => [
                'exclude_without:address',
                'required',
                'string',
            ],
            'address.address' => [
                'exclude_without:address',
                'required',
                'string'
            ],
            'address.district' => [
                'exclude_without:address',
                'required',
                function ($attribute, $value, $fail) use ($data) {
                    if ($data['address']['country'] === 'VN') {
                        if (Address::districtDoesntExist($data["address"]['province'], $value)) {
                            $fail('The district is invalid.');
                        }
                    }
                }
            ],
            'address.province' => [
                'exclude_without:address',
                'required',
                function ($attribute, $value, $fail) use ($data) {
                    if ($data['address']['country'] === 'VN') {
                        if (Address::provinceDoesntExist($value)) {
                            $fail('The district is invalid.');
                        }
                    }
                }
            ],
            'address.postal_code' => 'exclude_without:address|required_unless:address.country,VN',
            'address.country' => [
                'exclude_without:address',
                'required',
                function ($attribute, $value, $fail) {
                    if (Address::countryDoesntExist($value)) {
                        $fail('The country is invalid.');
                    }
                }
            ],
            'method' => [
                'required',
                'in:paypal,card,vnpay,bank'
            ]
        ], [
            'address.name.required' => 'The name field is required.',
            'address.phone_number.required' => 'The phone number field is required.',
            'address.address.required' => 'The address field is required.',
            'address.district.required' => 'The district field is required.',
            'address.province.required' => 'The province field is required.',
            'address.postal_code.required_unless' => 'The postal code field is required.',
            'address.country.required' => 'The country field is required.',
        ]);

        if (isset($validated['shipping_address_id'])) {
            $shippingAddress = ShippingAddress::find($validated['shipping_address_id']);
        } else {
            $address = $validated['address'];
            $newPhone = Address::country($address['country'])['dial'];
            if (strpos($address['phone_number'], "0") === 0) {
                $newPhone .= substr($address['phone_number'], 1);
            } else {
                $newPhone .= $address['phone_number'];
            }
            $address['phone_number'] = $newPhone;

            if ($address['country'] == "VN") {
                $address['postal_code'] = '';
            }

            $shippingAddress = new ShippingAddress();
            $shippingAddress->user_id = $user->user_id;
            $shippingAddress->fill($address);
            $shippingAddress->save();
        }

        $configuration = Configuration::find($validated['configuration_id']);

        $order = new Order();
        $order->configuration_id = $configuration->configuration_id;
        $order->shipping_address_id = $shippingAddress->shipping_address_id;
        $order->payment_method = $validated['method'];
        $order->status = 'pending';
        $order->amount = $configuration->total_amount;
        $order->shipping_fee = $shippingAddress->fee;
        $order->quantity = $validated['quantity'];
        $order->is_paid = false;

        $user->orders()->save($order);

        [$method, $info, $url] = match ($validated['method']) {
            'paypal', 'card' => Paypal::amount($order->total_amount)->create([
                'id' => $order->order_id
            ]),
            'vnpay' => Vnpay::amount(Currency::convert($order->total_amount, 'USD', 'VND'))->payqr()->create([
                'id' => $order->order_id
            ]),
            'bank' => Vnpay::amount(Currency::convert($order->total_amount, 'USD', 'VND'))->bank()->create([
                'id' => $order->order_id
            ]),
        };

        $payment = new Payment();
        $payment->order_id = $order->order_id;
        $payment->fill([
            'method' => $method,
            'info' => $info
        ]);
        $payment->save();

        return response()->json([
            'url' => $url
        ]);
    }

    public function return(string $payment)
    {
        $query = request()->query();
        $class = match ($payment) {
            'vnpay' => Vnpay::class,
            'paypal' => Paypal::class,
            default => null
        };
        if (!$class) {
            return [];
        }
        $details = $class::details($query);
        if (!$details) {
            return [];
        }
        $order = Order::find($details['id']);
        if ($order->is_paid) {
            return response()->json($order);
        }
        $payment = $order->payment;
        if (!$payment) {
            return response()->json($order);
        }
        if (empty($payment->info)) {
            $payment->info = $details['info'];
            $payment->save();
        }
        if ($class::success($query)) {
            $order->update([
                'is_paid' => true,
                'status' => OrderStatus::PROCESSING
            ]);
            $payment->info = $details['info'];
            $payment->save();
        }
        return response()->json($order);
    }
}
