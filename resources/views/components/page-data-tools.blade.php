@props([
    'module',
    'canImport' => true,
    'showTemplate' => true,
])

<div class="page-data-tools hms-no-print">
    <div class="page-data-tools-left">
        @if($canImport)
            <form
                method="POST"
                enctype="multipart/form-data"
                action="{{ route('admin.data-tools.import', $module) }}"
                class="bulk-upload-form"
            >
                @csrf

                <label class="btn btn-outline-primary">
                    <i class="fas fa-file-arrow-up"></i>
                    CSV Bulk Upload

                    <input
                        type="file"
                        name="file"
                        accept=".csv,text/csv"
                        hidden
                        onchange="this.form.submit()"
                    >
                </label>
            </form>
        @endif

        @if($showTemplate)
            <a
                href="{{ route('admin.data-tools.template', $module) }}"
                class="btn btn-outline-secondary"
            >
                <i class="fas fa-file-csv"></i>
                CSV Template
            </a>
        @endif
    </div>

    <div class="page-data-tools-right">
        <a
            href="{{ route('admin.data-tools.csv', $module) }}"
            class="btn btn-outline-primary"
        >
            <i class="fas fa-file-csv"></i>
            CSV
        </a>

        <a
            href="{{ route('admin.data-tools.pdf', $module) }}"
            class="btn btn-outline-primary"
            target="_blank"
        >
            <i class="fas fa-file-pdf"></i>
            PDF
        </a>

        <a
            href="{{ route('admin.data-tools.print', $module) }}"
            class="btn btn-outline-primary"
            target="_blank"
        >
            <i class="fas fa-print"></i>
            Print
        </a>
    </div>
</div>
