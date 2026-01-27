<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8" />
  <title>تقرير الدخل</title>
  <style>
    body { font-family: DejaVu Sans, Arial, sans-serif; direction: rtl; }
    table { width:100%; border-collapse: collapse }
    th, td { border:1px solid #ddd; padding:8px }
    th { background:#f6f6f6 }
  </style>
</head>
<body>
  <h1>تقرير الدخل</h1>
  <table>
    <thead>
      <tr><th>رقم الفاتورة</th><th>المريض</th><th>المبلغ</th><th>الحالة</th><th>تاريخ</th></tr>
    </thead>
    <tbody>
      @foreach($invoices as $inv)
        <tr>
          <td>{{ $inv->invoice_number }}</td>
          <td>{{ $inv->patient->first_name ?? '-' }}</td>
          <td>{{ $inv->total }}</td>
          <td>{{ $inv->status }}</td>
          <td>{{ $inv->created_at }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
