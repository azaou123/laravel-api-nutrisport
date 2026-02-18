<!DOCTYPE html>
<html>
<head>
    <title>Confirmation de commande</title>
</head>
<body>
    <h1>Merci pour votre commande {{ $order->shipping_full_name }} !</h1>
    <p>Votre commande #{{ $order->id }} a bien été enregistrée.</p>
    <p>Vous recevrez un email dès que le paiement sera confirmé.</p>

    <h3>Détails de la commande :</h3>
    <ul>
        @foreach($cart['items'] as $item)
            <li>{{ $item['quantity'] }} x {{ $item['name'] }} - {{ $item['price'] }} €</li>
        @endforeach
    </ul>
    <p><strong>Total : {{ $order->total }} €</strong></p>
</body>
</html>