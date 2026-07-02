{{-- Reusable modal that loads the damage create/edit form in an iframe so photo
     uploads keep working. Include this on pages that have a damages table. --}}
<div class="modal fade" id="damageEditModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="overflow:hidden;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                    style="position:absolute; right:14px; top:11px; z-index:20; color:#fff; opacity:.85; text-shadow:none; font-size:26px; line-height:1;">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="modal-body" style="padding:0;">
                <iframe id="damageEditFrame" src="about:blank" style="width:100%; height:80vh; max-height:620px; border:0; display:block;"></iframe>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ csrf_token() }}">
    // Open a create/edit damage URL inside the modal (append modal=1 handled by caller).
    window.openDamageModal = function (url) {
        $('#damageEditFrame').attr('src', url);
        $('#damageEditModal').modal('show');
    };

    // Intercept "edit damage" links in any damages table on the page.
    $(document).on('click', '.snipe-table a[href*="/damages/"][href$="/edit"]', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        window.openDamageModal(url + (url.indexOf('?') > -1 ? '&' : '?') + 'modal=1');
    });

    $('#damageEditModal').on('hidden.bs.modal', function () {
        $('#damageEditFrame').attr('src', 'about:blank');
    });

    // The form (inside the iframe) posts back when it saves or cancels.
    window.addEventListener('message', function (ev) {
        if (ev.data === 'damage-saved') {
            $('#damageEditModal').modal('hide');
            $('.snipe-table').each(function () {
                if (/damages/i.test(this.id)) {
                    try { $(this).bootstrapTable('refresh'); } catch (e) {}
                }
            });
        } else if (ev.data === 'damage-cancel') {
            $('#damageEditModal').modal('hide');
        }
    });
</script>
