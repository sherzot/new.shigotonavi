<div class="accordion-item">
    <h2 class="accordion-header" id="headingCareer">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCareer" aria-expanded="false" aria-controls="collapseCareer">
            職歴入力 (Career Information)
        </button>
    </h2>
    <div id="collapseCareer" class="accordion-collapse collapse" aria-labelledby="headingCareer" data-bs-parent="#resumeAccordion">
        <div class="accordion-body">
            @foreach ($careers as $index => $career)
                <div class="border p-3 mb-3 rounded">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">会社名</label>
                            <input type="text" class="form-control form-control-sm" wire:model.lazy="careers.{{ $index }}.company_name">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">資本金 (万円)</label>
                            <input type="number" class="form-control form-control-sm" wire:model.lazy="careers.{{ $index }}.capital">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">従業員数</label>
                            <input type="number" class="form-control form-control-sm" wire:model.lazy="careers.{{ $index }}.number_employees">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">入社年</label>
                            <input type="text" class="form-control form-control-sm" wire:model.lazy="careers.{{ $index }}.entry_day_year">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">入社月</label>
                            <input type="text" class="form-control form-control-sm" wire:model.lazy="careers.{{ $index }}.entry_day_month">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">退社年</label>
                            <input type="text" class="form-control form-control-sm" wire:model.lazy="careers.{{ $index }}.retire_day_year">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">退社月</label>
                            <input type="text" class="form-control form-control-sm" wire:model.lazy="careers.{{ $index }}.retire_day_month">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">職務内容</label>
                            <textarea class="form-control form-control-sm" wire:model.lazy="careers.{{ $index }}.job_type_detail" rows="2"></textarea>
                        </div>
                    </div>

                    <div class="mt-2 text-end">
                        <button type="button" class="btn btn-outline-danger btn-sm" wire:click.prevent="removeCareer({{ $index }})">削除</button>
                    </div>
                </div>
            @endforeach

            <div class="text-end">
                <button type="button" class="btn btn-outline-primary btn-sm" wire:click.prevent="addCareer">追加</button>
            </div>
        </div>
    </div>
</div>