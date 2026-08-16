{{-- Reuse order modal from index page pattern --}}
<div class="modal fade p2p-ui-modal" id="p2pOrderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">@lang('Start Trade')</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('user.p2p.orders.store') }}">
        @csrf
        <div class="modal-body">
          <input type="hidden" name="offer_id" id="p2p_offer_id">
          <div class="mb-2">
            <label class="form-label">@lang('Amount') (<span id="p2p_currency">-</span>)</label>
            <input type="text" inputmode="decimal" class="form-control form-control-sm" name="amount" id="p2p_amount" required oninput="this.value = validateDouble(this.value)">
            <div class="form-text">
                <span id="p2p_minmax"></span>
            </div>
          </div>
          <div class="small text-muted" id="p2p_info"></div>
        </div>
        <div class="modal-footer flex-column">
          <button type="button" class="btn btn-light-primary btn-sm w-100" data-bs-dismiss="modal">
              <i class="fas fa-times me-1"></i> @lang('Close')
          </button>
          <button type="submit" class="btn btn-base w-100 mt-4 submit-btn">
              <i class="fas fa-check me-1"></i> @lang('Confirm')
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
