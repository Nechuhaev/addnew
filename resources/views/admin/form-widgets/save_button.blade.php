<div class="card">
    <div class="card-body">
        <div class="form-group row" style="margin-bottom: 0">
            <div class="col-8">
                <button class="btn btn-success">Сохранить</button>
            </div>
            @if($delete_action_route ?? null)

            <div class="col-4 text-right">
                <button formmethod="post"
                        formaction="{{ $delete_action_route }}"
                        onclick="return confirm('Запись будет удалена из базы данных и этого не вернуть.')"
                        class="text-danger"><i class="mdi mdi-24px mdi-delete"></i></button>
            </div>

            @endif
        </div>
    </div>
</div>
