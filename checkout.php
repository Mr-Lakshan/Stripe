<?php
require 'vendor/autoload.php';

\Stripe\Stripe::setApiKey('sk_test_51LSAwGSH0s7VQxZwk82nzQIwibACppRui1EtJPwyQ0yhBjsOnZBWZi3BInU3nKvqm1vNRVmx1SxBkKQNkqNjx9ej00lTdKM34n');


$data = json_decode(file_get_contents('php://input'), true);


if (isset($data['token'], $data['name'], $data['email'], $data['address'], $data['city'], $data['state'], $data['zip'], $data['country'], $data['amount'])) {
    try {

        $customer = \Stripe\Customer::create([
            'source' => $data['token'],
            'name' => $data['name'],
            'email' => $data['email'],
            'address' => [
                'line1' => $data['address'],
                'city' => $data['city'],
                'state' => $data['state'],
                'postal_code' => $data['zip'],
                'country' => $data['country'],
            ],
        ]);

        $product = $data['product'];
        $charge = \Stripe\PaymentIntent::create([
            'amount' => $data['amount'],
            'currency' => 'usd',
            'customer' => $customer->id,
            'description' => 'Payment for Order' . $product,
        ]);


        // Return success response
        echo json_encode(['success' => true, 'charge' => $charge]);
        session_start();
        session_unset();
        session_destroy();
    } catch (\Stripe\Exception\ApiErrorException $e) {
        // Return error response
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Missing customer details or amount']);
}
