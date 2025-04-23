<!-- Row -->
@push('css')
<link rel="stylesheet" type="text/css"
  href="{{ asset('/') }}dashboard/assets/node_modules/datatables.net-bs4/css/dataTables.bootstrap4.css">
<link rel="stylesheet" type="text/css"
  href="{{ asset('/') }}dashboard/assets/node_modules/datatables.net-bs4/css/responsive.dataTables.min.css">
<style>
  table{
    width: 100% !important;
  }
  table.dataTable {
    margin-top: 0px !important;
  }
  .dataTable > thead > tr > th:last-child[class*="sort"]:before,
  .dataTable > thead > tr > th:last-child[class*="sort"]:after {
      content: "" !important;
  }
</style>
@endpush
@push('js')

<script defer sync src="{{ asset('dashboard') }}/js/app-datatables.js"></script>

<script>
$(document).ready(function() {
  var table= $('#example23').DataTable({
    dom: 'frlBtip',
    "lengthMenu": [[10, 25, 50,100,200, "All"],[10, 25, 50,100,200, "{{translate_lang('all')}}"]],
    buttons: [
      // 'colvis',
      {
        extend: 'copy',
        text: "{{ translate_lang('copy') }}",
        exportOptions: {columns: 'th:not(:last-child)'},
      },
      {
        extend: 'pdfHtml5',
        text: 'Pdf',
        exportOptions: {columns: 'th:not(:last-child)'},
      },
      {
        extend: 'excel',
        text: "{{ translate_lang('excel') }}",
        exportOptions: {columns: 'th:not(:last-child)'},
      },
      {
        extend: 'print',
        text: "{{ translate_lang('print') }}",
        exportOptions: {columns: 'th:not(:last-child)'},
      },
      {
        action: function () {
          table.search('').columns().search('').draw();
        },
        className: 'buttons-reset',
      },
    ],
    language: {
      "infoFiltered":   "(filtered from _MAX_ total entries)",
      "loadingRecords": "Loading...",
      "processing":     "",
      "zeroRecords":    "No matching records found",
      "emptyTable":     "No data available in table",
      "info":           "{{ translate_lang('showing')}} _START_ {{ translate_lang('to')}} _END_ {{ translate_lang('from')}} _TOTAL_ {{ translate_lang('entries')}}",
      "infoEmpty":      "Showing 0 to 0 of 0 entries",
      "infoFiltered":   "(filtered from _MAX_ total entries)",
      "zeroRecords":    "No matching records found",
      "paginate": {
          "first":      "{{ translate_lang('first')}}",
          "last":       "{{ translate_lang('last')}}",
          "next":       "{{ translate_lang('next')}}",
          "previous":   "{{ translate_lang('previous')}}",
          "showing":   "{{ translate_lang('showing')}}",
          "entries":   "{{ translate_lang('entries')}}",
      },
      "search":"{{translate_lang('search')}} ",
      "lengthMenu":     "{{translate_lang('show')}} _MENU_ {{translate_lang('entries')}}",
    },
    "order": [[ 0, "desc" ]],
    responsive: true,
    searching: true,
    // scrollCollapse: true,
    // scrollX: true,
    // scrollY: 300,
  });
  $('.dt-buttons,.dataTables_filter ,.dataTables_length').wrapAll("<div class='card-header'></div>");
  $('.buttons-copy, .buttons-csv, .buttons-print, .buttons-pdf, .buttons-excel, .buttons-reset').addClass('btn btn-primary mr-1 mb-2');
  $('.buttons-reset').html("<i class='fa fa-refresh text-sm'></i>");
  $('.buttons-reset').html("<i class='fa fa-refresh text-sm'></i>");
});
</script>
@endpush
