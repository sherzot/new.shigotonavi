<div class="accordion-item">
    <h2 class="accordion-header" id="headingEducation">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseEducation" aria-expanded="false" aria-controls="collapseEducation">
            学歴入力
        </button>
    </h2>
    <div id="collapseEducation" class="accordion-collapse collapse" aria-labelledby="headingEducation"
         data-bs-parent="#resumeAccordion">
        <div class="accordion-body">
            @foreach ($educations as $index => $education)
                <div class="border rounded p-3 mb-3">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">学校名</label>
                            <input type="text" class="form-control form-control-sm"
                                   wire:model.lazy="educations.{{ $index }}.school_name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">学校種別コード</label>
                            <input type="text" class="form-control form-control-sm"
                                   wire:model.lazy="educations.{{ $index }}.school_type_code">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">入学年</label>
                            <input type="text" class="form-control form-control-sm"
                                   wire:model.lazy="educations.{{ $index }}.entry_day_year">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">入学月</label>
                            <input type="text" class="form-control form-control-sm"
                                   wire:model.lazy="educations.{{ $index }}.entry_day_month">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">卒業年</label>
                            <input type="text" class="form-control form-control-sm"
                                   wire:model.lazy="educations.{{ $index }}.graduate_day_year">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">卒業月</label>
                            <input type="text" class="form-control form-control-sm"
                                   wire:model.lazy="educations.{{ $index }}.graduate_day_month">
                        </div>
                        <div class="col-md-12 text-end">
                            <button type="button" class="btn btn-danger btn-sm mt-2"
                                    wire:click.prevent="removeEducation({{ $index }})">
                                削除
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="text-end">
                <button type="button" class="btn btn-outline-primary btn-sm" wire:click.prevent="addEducation">
                    + 学歴追加
                </button>
            </div>
        </div>
    </div>
</div>