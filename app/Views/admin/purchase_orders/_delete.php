<form id="deletePOForm" method="post" style="display:none">
    <?= csrf_field() ?>
</form>
<script>
function deletePO(id, number) {
    var form = document.getElementById('deletePOForm');
    form.action = '<?= base_url('admin/purchase-orders/delete') ?>/' + id;
    var settings = {
        title: 'Delete ' + number + '?',
        text: 'This permanently removes the purchase order and its items. It cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        confirmButtonColor: '#c62828',
        cancelButtonColor: '#5b6672',
        focusCancel: true
    };
    if (window.Swal) {
        Swal.fire(settings).then(function (r) { if (r.isConfirmed) form.submit(); });
    } else if (window.confirm(settings.title + ' ' + settings.text)) {
        form.submit();
    }
}
</script>
