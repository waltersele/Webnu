@if($company->reservation)
<div class="modal fade wn-reservation-modal" id="tableReservationModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('table_reservation') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reserva de mesa</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="company_email" value="{{ $company->email }}">
                    <div class="form-group">
                        <label>Fecha</label>
                        <input type="date" class="form-control" name="date" required>
                    </div>
                    <div class="form-group">
                        <label>Hora</label>
                        <input type="time" class="form-control" name="hour" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="name" class="form-control" placeholder="Nombre *" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="phone" class="form-control" placeholder="Teléfono *" required>
                    </div>
                    <div class="form-group mb-0">
                        <input type="email" name="email" class="form-control" placeholder="Email">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-block">Enviar reserva</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
