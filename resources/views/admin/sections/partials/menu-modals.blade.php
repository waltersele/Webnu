    <div class="webnu-menu-modals">
        <div class="modal fade" id="modal-add-section">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Nueva secci�n</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <form method="POST" action="{{ route('admin.sections.store') }}">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Nombre</label>
                                <input type="text" name="name" autofocus value="{{ old('name') }}" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" placeholder="Ej. Entrantes" required>
                                {!! $errors->first('name', '<span class="error invalid-feedback">:message</span>') !!}
                            </div>
                            <div class="form-group mb-0">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" name="section_enabled" id="section-add-enabled-switch" value="1" checked>
                                    <label class="form-check-label" for="section-add-enabled-switch">Visible en la carta</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="webnu-btn webnu-btn--ghost" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="webnu-btn webnu-btn--primary">Crear secci�n</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
            <!-- Modify modal - Seccion -->
            <div class="modal fade" id="modal-modify-section">
                <div class="modal-dialog">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Modificar secci�n</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <form role="form" method="POST" action="{{ route('admin.sections.update') }}">
                        {{ csrf_field() }} {{ method_field('PUT') }}
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Nombre Secci�n</label>
                                <input type="text" name="name" id="modify-name" autofocus value="{{ old('name') }}" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" placeholder="Nombre secci�n" required>
                                {!! $errors->first('name', '<span class="error invalid-feedback">:message</span>') !!}
                                <input type="hidden" value="" name="sectionid" id="modify-id">
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" name="section_enabled" id="section-modify-enabled-switch" value="1" checked="checked">
                                    <label class="form-check-label" for="section-modify-enabled-switch">Habilitado</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="submit" class="btn btn-success">Modificar secci�n</button>
                            <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </form>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- /.modal -->
            <!-- Delete modal - Seccion -->
            <div class="modal fade" id="modal-delete-section">
                <div class="modal-dialog">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Eliminar secci�n</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <form role="form" method="POST" action="{{ route('admin.sections.delete') }}">
                        {{ csrf_field() }} {{ method_field('DELETE') }}
                        <div class="modal-body">
                            <div class="mb-3">
                                <p>�Est�s seguro de eliminar la secci�n?</p>
                                <input type="hidden" value="" name="sectionid" id="delete-id">
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="submit" class="btn btn-success">Eliminar secci�n</button>
                            <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </form>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- /.modal -->
            <!-- Add modal - Producto -->
            <div class="modal fade" id="modal-add-product">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Crear producto</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <form role="form" enctype="multipart/form-data" method="POST" id="modal-add-product-form" action="{{ route('admin.products.store') }}">
                            {{ csrf_field() }}
                            <div class="modal-body">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" name="product_add_enabled" id="product-add-enabled-switch" value="1" checked="checked">
                                        <label class="form-check-label" for="product-add-enabled-switch">Habilitado</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Nombre</label>
                                    <input type="text" name="product_add_name" id="product-add-name" autofocus value="" class="form-control" placeholder="Nombre producto" required>
                                    <!--<span class="error invalid-feedback">:message</span>-->
                                </div>
                                <div class="mb-3">
                                    <label>Descripci�n</label>
                                    <textarea class="form-control" name="product_add_description" id="product-add-description" rows="3" placeholder="Descripci�n"></textarea>
                                </div>
                                @include('admin.sections.partials.product-media', ['mode' => 'add'])
                                <div class="mb-3">
                                    <label>Precio/unidad</label>
                                    <div class="input-group">
                                        <input type="text" name="product_add_price_unit" id="product-add-price-unit" value="" class="form-control" placeholder="Precio unidad" required>
                                        <!--<span class="error invalid-feedback">:message</span>-->
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-euro-sign"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <small>Introducir solamente n�meros</small>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" name="product_add_enable_price_portion" value="1" id="product-add-enable-price-portion-switch">
                                        <label class="form-check-label" for="product-add-enable-price-portion-switch">Habilitar precio 1/2 raci�n</label>
                                    </div>
                                </div>
                                <div class="form-group hidden">
                                    <label>Precio 1/2 raci�n</label>
                                    <div class="input-group">
                                        <input type="text" name="product_add_price_portion" id="product-add-price-portion" value="" class="form-control" placeholder="Precio 1/2 raci�n">
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-euro-sign"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <small>Introducir solamente n�meros</small>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" name="product_add_individual_sale" value="1" id="product-add-sold-by-piece-switch">
                                        <label class="form-check-label" for="product-add-sold-by-piece-switch">Este producto se vende por unidades</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Al�rgenos</label>
                                    <select class="select2-allergens select2" name="allergens[]" multiple="multiple" data-placeholder="Seleccionar alérgenos" style="width: 100%;">
                                        @foreach ($allergens as $allergen)
                                            <option value="{{ $allergen->id }}">{{ $allergen->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <input type="hidden" name="product_add_section_id" id="product-add-section-id">
                            </div>
                            <div class="modal-footer justify-content-between">
                                <button type="submit" class="appao-btn2 btn-block">A�adir producto</button>
                                <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cancelar</button>
                            </div>
                        </form>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- /.modal -->
            <!-- Modify modal - Producto -->
            <div class="modal fade" id="modal-modify-product">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Modificar producto</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <form role="form" enctype="multipart/form-data" method="POST" id="modal-modify-product-form" action="{{ route('admin.products.update') }}">
                            {{ csrf_field() }} {{ method_field('PUT') }}
                            <div class="modal-body">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" name="product_modify_enabled" id="product-modify-enabled" value="1">
                                        <label class="form-check-label" for="product-modify-enabled">Habilitado</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Nombre</label>
                                    <input type="text" name="product_modify_name" id="product-modify-name" autofocus value="" class="form-control" placeholder="Nombre producto" required>
                                    <!--<span class="error invalid-feedback">:message</span>-->
                                </div>
                                <div class="mb-3">
                                    <label>Descripci�n</label>
                                    <textarea class="form-control" name="product_modify_description" id="product-modify-description" rows="3" placeholder="Descripci�n"></textarea>
                                </div>
                                @include('admin.sections.partials.product-media', ['mode' => 'modify'])
                                <div class="mb-3">
                                    <label>Precio/unidad</label>
                                    <div class="input-group">
                                        <input type="text" name="product_modify_price_unit" id="product-modify-price-unit" value="" class="form-control" placeholder="Precio unidad" required>
                                        <!--<span class="error invalid-feedback">:message</span>-->
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-euro-sign"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <small>Introducir solamente n�meros</small>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" name="product_modify_enable_price_portion" value="1" id="product-modify-enable-price-portion-switch">
                                        <label class="form-check-label" for="product-modify-enable-price-portion-switch">Habilitar precio 1/2 raci�n</label>
                                    </div>
                                </div>
                                <div class="form-group hidden">
                                    <label>Precio 1/2 raci�n</label>
                                    <div class="input-group">
                                        <input type="text" name="product_modify_price_portion" id="product-modify-price-portion" value="" class="form-control" placeholder="Precio 1/2 raci�n">
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-euro-sign"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <small>Introducir solamente n�meros</small>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" name="product_modify_individual_sale" value="1" id="product-modify-individual-sale">
                                        <label class="form-check-label" for="product-modify-individual-sale">Este producto se vende por unidades</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Al�rgenos</label>
                                    <select class="select2-allergens select2" id="product-modify-allergens" name="allergens[]" multiple="multiple" data-placeholder="Seleccionar alérgenos" style="width: 100%;">
                                        @foreach ($allergens as $allergen)
                                            <option value="{{ $allergen->id }}">{{ $allergen->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <input type="hidden" name="product_id" id="product-modify-id">
                                <input type="hidden" name="product_modify_section_id" id="product-modify-section-id">
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="appao-btn2 bnt-block">Modificar producto</button>
                                <hr class="divider">
                                <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cancelar</button>
                            </div>
                        </form>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- /.modal -->
            <!-- Delete modal - Producto -->
            <div class="modal fade" id="modal-delete-product">
                <div class="modal-dialog">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Eliminar producto</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <form role="form" method="POST" action="{{ route('admin.products.delete') }}">
                        {{ csrf_field() }} {{ method_field('DELETE') }}
                        <div class="modal-body">
                            <div class="mb-3">
                                <p>�Est�s seguro de eliminar el producto?</p>
                                <input type="hidden" value="" name="productid" id="product-delete-id">
                                <input type="hidden" value="" name="product_delete_section_id" id="product-delete-section-id">
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="submit" class="btn btn-success">Eliminar producto</button>
                            <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </form>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- /.modal -->

