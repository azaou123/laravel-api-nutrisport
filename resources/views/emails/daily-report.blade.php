<!DOCTYPE html>
<html>
<head>
    <title>Rapport quotidien des ventes</title>
</head>
<body>
    <h1>Rapport des ventes du {{ $report['date'] }}</h1>

    @if(!empty($report['best_selling']))
        <h3>Produit le plus vendu (quantité)</h3>
        <p>{{ $report['best_selling']['name'] }} : {{ $report['best_selling']['quantity'] }} unités</p>

        <h3>Produit le moins vendu (quantité)</h3>
        <p>{{ $report['worst_selling']['name'] ?? 'N/A' }} : {{ $report['worst_selling']['quantity'] ?? 0 }} unités</p>

        <h3>Produit avec le CA max</h3>
        <p>{{ $report['max_revenue']['name'] }} : {{ number_format($report['max_revenue']['revenue'], 2) }} €</p>

        <h3>Produit avec le CA min</h3>
        <p>{{ $report['min_revenue']['name'] ?? 'N/A' }} : {{ number_format($report['min_revenue']['revenue'] ?? 0, 2) }} €</p>
    @else
        <p>Aucune vente enregistrée hier.</p>
    @endif

    <h3>Chiffre d'affaires par site</h3>
    <ul>
        @foreach($report['revenue_per_site'] as $siteName => $total)
            <li>{{ $siteName }} : {{ number_format($total, 2) }} €</li>
        @endforeach
        @if(empty($report['revenue_per_site']))
            <li>Aucun chiffre</li>
        @endif
    </ul>
</body>
</html>