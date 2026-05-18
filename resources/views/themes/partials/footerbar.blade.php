{{-- FIXED BOTTOM BAR --}}

@if($company->whatsapp && $company->reservation)

    <div class="fixed-bottom-bar text-center">
        <div class="container">
            <div class="row">
            <div class="col-6"> <a class="btn btn-block whatsapp-btn" href="https://wa.me/34{{$company->whatsapp}}"><i class="fab fa-whatsapp" aria-hidden="true"></i> Whatsapp</a></div>
                <div class="col-6"> <a class="btn btn-block" data-toggle="modal" data-target="#tableReservationModal">Reservar mesa</a></div>
            </div>
        </div>
    </div>

    @elseif($company->whatsapp && !$company->reservation)

    <div class="fixed-bottom-bar text-center">
        <div class="container">
            <div class="row">
                <div class="col-12"> <a class="btn btn-block whatsapp-btn"><i class="fab fa-whatsapp" aria-hidden="true"></i > Whatsapp</a></div>
            </div>
        </div>
    </div>

    @elseif(!$company->whatsapp && $company->reservation)

    <div class="fixed-bottom-bar text-center">
        <div class="container">
            <div class="row">
                <div class="col-12"> <a class="btn btn-block" data-toggle="modal" data-target="#tableReservationModal">¡Reservar mesa!</a></div>
            </div>
        </div>
    </div>




@endif



{{-- POP UP RESERVATION --}}

 <!-- Modal -->
 <div class="modal fade" id="tableReservationModal" tabindex="-1" role="dialog" aria-labelledby="tableReservationModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="contact-form" action="{{ route('table_reservation') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="tableReservationModalLabel">Reserva de mesa</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="company_email" value="{{ $company->email }}">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Selecciona la fecha</label>
                                <div class='input-group date' id='date-picker'>
                                <input type='date' class="form-control" id="date" name="date" required/>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Selecciona la hora</label>
                                <div class='input-group date' id='hour-picker'>
                                <input type='time' class="form-control" id="hour" name="hour" required/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <!-- text input -->
                            <div class="form-group">
                                <input type="text" name="name" class="form-control" placeholder="Nombre *" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <!-- text input -->
                            <div class="form-group">
                                <input type="text" name="phone" class="form-control" placeholder="Teléfono *" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <!-- text input -->
                            <div class="form-group">
                                <input type="email" name="email" class="form-control" placeholder="Email">
                            </div>
                        </div>
                    </div>
                    <small>*El restaurante te confirmará la reserva por medio de tu contacto facilitado</small>
                </div>
                
                <div class="modal-footer">
                    <button type="submit" class="btn btn-block reservation-send-btn">Reservar</button>
                    <button type="button" class="btn " data-dismiss="modal">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>