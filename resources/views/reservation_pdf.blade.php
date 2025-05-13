<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reservation Details</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 40px;
            color: #333;
            font-size: 14px;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .header h2 {
            margin-bottom: 5px;
            font-size: 24px;
            color: #2c3e50;
        }
        .header p {
            font-size: 14px;
            color: #555;
        }
        .section {
            margin-bottom: 25px;
        }
        .section h3 {
            font-size: 16px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            color: #2c3e50;
        }
        .label {
            font-weight: bold;
            color: #444;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .details-table th,
        .details-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .details-table th {
            background-color: #f7f7f7;
            font-weight: bold;
        }
        ul {
            margin: 5px 0 0 20px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Reservation Confirmation</h2>
        <p>Thank you for choosing LocaMaroc!</p>
    </div>

    <div class="section">
        <h3>Customer Information</h3>
        <div><span class="label">Name:</span> {{ $data['customerInfo']['fullName'] ?? '' }}</div>
        <div><span class="label">Email:</span> {{ $data['customerInfo']['email'] ?? '' }}</div>
        <div><span class="label">Phone:</span> {{ $data['customerInfo']['phone'] ?? '' }}</div>
        <div><span class="label">Age:</span> {{ $data['customerInfo']['age'] ?? '' }}</div>
        <div><span class="label">Driver License:</span> {{ $data['customerInfo']['driverLicense'] ?? '' }}</div>
    </div>

    <div class="section">
        <h3>Reservation Details</h3>
        <div><span class="label">Car:</span> {{ $data['car']['brand'] ?? '' }} {{ $data['car']['model'] ?? '' }}</div>
        <div><span class="label">Pickup Location:</span> {{ $data['pickupLocation'] ?? '' }}</div>
        <div><span class="label">Return Location:</span> {{ $data['returnLocation'] ?? '' }}</div>
        <div><span class="label">Pickup Date & Time:</span> {{ $data['pickupDate'] ?? '' }} {{ $data['pickupTime'] ?? '' }}</div>
        <div><span class="label">Return Date & Time:</span> {{ $data['returnDate'] ?? '' }} {{ $data['returnTime'] ?? '' }}</div>
        <div><span class="label">Driver Option:</span> {{ $data['driver'] === 'self' ? 'Self-Drive' : 'With Professional Driver' }}</div>
    </div>

    <div class="section">
        <h3>Extras</h3>
        <div><span class="label">Accessories:</span></div>
        @if(!empty($data['accessories']))
            <ul>
                @foreach($data['accessories'] as $acc)
                    <li>{{ $acc }}</li>
                @endforeach
            </ul>
        @else
            <div>None</div>
        @endif
        <div><span class="label">Insurance:</span> {{ $data['insurance'] ?? 'None' }}</div>
    </div>

    <div class="section">
        <h3>Payment</h3>
        <div><span class="label">Total Cost:</span> {{ $data['totalCost'] ?? '' }} DH</div>
    </div>

    <div class="footer">
        <small>Generated on {{ date('Y-m-d H:i') }}</small>
    </div>
</body>
</html>
