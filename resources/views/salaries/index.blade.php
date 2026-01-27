@extends('layouts.app')

@section('content')
<div class="space-y-4">
  <div class="flex items-center justify-between">
    <h2 class="text-xl font-bold">الرواتب</h2>
    <div class="flex gap-2">
      <button @click="generate()" class="px-3 py-2 bg-indigo-600 text-white rounded">توليد كشوف الرواتب (شهر)</button>
    </div>
  </div>

  <div id="sheets" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
</div>

<script>
async function loadSalaries(){
  const res = await fetch('/api/salaries', { credentials: 'same-origin' });
  const data = await res.json();
  const list = data.data || data;
  document.getElementById('sheets').innerHTML = list.map(s => `
    <div class="bg-white p-4 rounded shadow">
      <h3 class="font-semibold">${s.period}</h3>
      <div class="text-sm text-gray-500">إجمالي: ${s.total}</div>
      <div class="mt-3 text-sm">
        ${s.payments.map(p => `<div class=\"py-1 border-t\">${p.user.name} - ${p.total_paid}</div>`).join('')}
      </div>
    </div>
  `).join('');
}

async function generate(){
  const r = await fetch('/api/salaries/generate', { method:'POST', credentials: 'same-origin', headers:{'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')} });
  const json = await r.json();
  alert('Generated salary sheet for ' + json.period);
  loadSalaries();
}

loadSalaries();
</script>

@endsection
