<!DOCTYPE html>
<html>
<head>
    <title>Nouvelle commande</title>
</head>
<body>
    <h1>Nouvelle commande #{{ $order->id }}</h1>
    <p><strong>Client :</strong> {{ $order->shipping_full_name }}</p>
    <p><strong>Email :</strong> {{ $order->user->email }}</p>
    <p><strong>Adresse :</strong> {{ $order->shipping_address }}, {{ $order->shipping_city }}, {{ $order->shipping_country }}</p>
    <p><strong>Total :</strong> {{ $order->total }} €</p>
    <p><strong>Statut :</strong> {{ $order->status }}</p>
    <h3>Produits :</h3>
    <ul>
        @foreach($order->items as $item)
            <li>{{ $item->quantity }} x {{ $item->product->name }} - {{ $item->price }} € (total {{ $item->total }} €)</li>
        @endforeach
    </ul>
</body>
</html>