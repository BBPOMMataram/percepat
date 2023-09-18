@extends('layout.app')
@section('content')
@parent
<div class="row mt-3">
  <div class="col-12">
    @if (auth()->user()->level === 'admin')
    <a href="{{ route('pembelian.create') }}" class="btn btn-light mb-3">ADD</a>
    @endif
    <div class="table-responsive">
      <table class="table table-striped" id="dttable">
        <thead>
          <th>Actions</th>
          <th>No</th>
          <th>Name</th>
          <th>Expired</th>
          <th>Jumlah</th>
          <th>Vendor</th>
          <th>Tgl Penerimaan</th>
        </thead>
      </table>
    </div>
  </div>
</div>
@endsection
@push('scripts')

<script src="{{asset('vendor/assets/plugins/jquery-ui/jquery-ui.min.js')}}"></script>
<script>
  $(function(){
    
      const dttable = $('#dttable').DataTable({
          responsive: true,
          serverSide: true,
          ajax: {
            url: "{{ route('dt_pembelian') }}"
          },
          columns: [
            { data: 'actions', className: 'text-center' },
            { data: 'DT_RowIndex' },
            { data: 'barang.name', className: 'text-wrap' },
            { data: 'expired', render: function($data){ return $data ? $data : '-' ;}  },
            { data: 'jumlah' },
            { data: 'vendor' },
            { data: 'created_at' },
            { data: 'id', visible: false}
          ],
  createdRow: function (row, data, dataIndex) {
    // Menambahkan atribut data-id ke setiap baris
    $(row).attr('data-id', data.id);
    $(row).attr('data-barang', data.barang.name);
  },
          initComplete: function () {
            // Setelah tabel selesai dimuat, inisialisasi Sortable pada tbody
            $('tbody').sortable({
              axis: 'y', // Mengizinkan hanya pergeseran vertikal (atas-ke-bawah)
              containment: 'table', // Mengizinkan pergeseran dalam batas tabel
              cursor: 'move', // Mengganti kursor saat menggeser
              update: function (event, ui) {
                // Dijalankan saat posisi baris berubah
                // Anda dapat mengirimkan permintaan AJAX untuk memperbarui urutan di server di sini
                const newOrder = []

                // Mendapatkan data ID dan data barang dari semua elemen sebelum diurutkan
                const rows = $(this).find('tr');
                
                // Mendapatkan ID dalam urutan yang sudah diurutkan
                const sortedIds = rows.map(function() {
                  return $(this).data('id');
                }).get().sort(function(a, b) {
                  return a - b;
                });
                
                rows.each(function(index) {
                  const id = sortedIds[index];
                  const barang = $(this).data('barang');
                  newOrder.push({ id, barang });
                });
                // $(this).find('tr').each(function(i){
                //   const id = $(this).data('id');
                //   const barang = $(this).data('barang');
                //   newOrder.push({ id, barang});
                // })

                $.ajax({
                type: 'POST',
                url: '{{ route('testing') }}', // Gantilah dengan URL yang sesuai
                data: {
                    newOrder: newOrder,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    console.log(response);
                }
            });
              }
            }).disableSelection(); // Mencegah teks dalam sel dipilih saat menggeser
          }
        })

        $('#dttable').on('click', '.delete', function(e){
          e.preventDefault();
          const rowData = dttable.row($(this).parents('tr')).data();
          const id = rowData['id'];
          Swal.fire({
            title: 'Deletion Confirmation',
            text: 'Really to delete this item ?',
            icon: 'question',
            showCancelButton: true,
          }).then(function(val){
            if(val.isConfirmed){
              $.ajax({
                type: "delete",
                url: "pembelian/" + id,
                data: {
                  _token: "{{ csrf_token() }}"
                },
                success: function (response) {
                  if(response.status){
                    Swal.fire({
                      title: 'Success',
                      text: response.msg,
                      icon: 'success',
                    })
                  }
                  dttable.ajax.reload(null, false);
                }
              });
            }
          })
        })
      });
</script>
@endpush