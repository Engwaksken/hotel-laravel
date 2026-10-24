@extends('layouts.app')

@section('title', 'Website & Services')
@section('page_title', 'Website & Services')

@section('content')
<div class="page-head">
    <div>
        <h1>Website &amp; services</h1>
        <p>Manage public homepage banners and image-based guest services.</p>
    </div>
    <a href="{{ route('client.landing') }}" class="btn btn-outline-primary" target="_blank">
        <i class="fas fa-arrow-up-right-from-square"></i>
        View Website
    </a>
</div>

<div class="website-admin-tabs">
    <button type="button" class="active" data-web-tab="banners">Homepage Banners</button>
    <button type="button" data-web-tab="services">Hotel Services</button>
</div>

<section class="website-admin-panel active" data-web-panel="banners">
    <div class="card">
        <h2>Add homepage banner</h2>

        <form method="POST" action="{{ route('admin.website.banners.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="form-grid">
                <div>
                    <label>Heading</label>
                    <input type="text" name="title" required>
                </div>

                <div>
                    <label>Banner image</label>
                    <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" required>
                </div>

                <div>
                    <label>Button text</label>
                    <input type="text" name="button_text" placeholder="Book Your Stay">
                </div>

                <div>
                    <label>Button URL</label>
                    <input type="text" name="button_url" placeholder="/hotel/...">
                </div>

                <div>
                    <label>Display order</label>
                    <input type="number" name="display_order" value="0" min="0">
                </div>
            </div>

            <div style="margin-top:10px">
                <label>Subtitle</label>
                <textarea name="subtitle" rows="3"></textarea>
            </div>

            <label class="check-row">
                <input type="checkbox" name="is_active" value="1" checked>
                Active
            </label>

            <button class="btn btn-primary" type="submit">
                <i class="fas fa-image"></i>
                Add Banner
            </button>
        </form>
    </div>

    <div class="admin-content-grid">
        @foreach($banners as $banner)
            <article class="website-content-card">
                <img src="{{ asset('storage/'.$banner->image_path) }}" alt="{{ $banner->title }}">

                <div>
                    <strong>{{ $banner->title }}</strong>
                    <small>{{ $banner->is_active ? 'Active' : 'Hidden' }}</small>
                </div>

                <form method="POST" action="{{ route('admin.website.banners.destroy', $banner->id) }}" onsubmit="return confirm('Delete this banner?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </article>
        @endforeach
    </div>
</section>

<section class="website-admin-panel" data-web-panel="services">
    <div class="card">
        <h2>Add hotel service</h2>

        <form method="POST" action="{{ route('admin.website.services.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="form-grid">
                <div>
                    <label>Service name</label>
                    <input type="text" name="name" required>
                </div>

                <div>
                    <label>Service image</label>
                    <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
                </div>

                <div>
                    <label>Price</label>
                    <input type="number" name="price" step="0.01" min="0">
                </div>

                <div>
                    <label>Price label</label>
                    <input type="text" name="price_label" placeholder="From UGX 80,000">
                </div>

                <div>
                    <label>Button text</label>
                    <input type="text" name="button_text" placeholder="Book / Enquire">
                </div>

                <div>
                    <label>Button URL</label>
                    <input type="text" name="button_url" placeholder="/contact">
                </div>

                <div>
                    <label>Display order</label>
                    <input type="number" name="display_order" value="0" min="0">
                </div>
            </div>

            <div style="margin-top:10px">
                <label>Short description</label>
                <input type="text" name="short_description" maxlength="300">
            </div>

            <div style="margin-top:10px">
                <label>Full description</label>
                <textarea name="description" rows="5"></textarea>
            </div>

            <div class="check-row-group">
                <label class="check-row">
                    <input type="checkbox" name="show_on_homepage" value="1" checked>
                    Show on homepage
                </label>

                <label class="check-row">
                    <input type="checkbox" name="is_active" value="1" checked>
                    Active
                </label>
            </div>

            <button class="btn btn-primary" type="submit">
                <i class="fas fa-plus"></i>
                Add Service
            </button>
        </form>
    </div>

    <div class="admin-content-grid">
        @foreach($services as $service)
            <article class="website-content-card">
                @if($service->image_path)
                    <img src="{{ asset('storage/'.$service->image_path) }}" alt="{{ $service->name }}">
                @else
                    <div class="content-image-placeholder"><i class="fas fa-bell-concierge"></i></div>
                @endif

                <div>
                    <strong>{{ $service->name }}</strong>
                    <small>{{ $service->is_active ? 'Active' : 'Hidden' }}</small>
                </div>

                <form method="POST" action="{{ route('admin.website.services.destroy', $service->id) }}" onsubmit="return confirm('Delete this service?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </article>
        @endforeach
    </div>
</section>
@endsection

@push('styles')
<style>
.website-admin-tabs{display:flex;gap:5px;margin-bottom:12px;border-bottom:1px solid var(--hms-border)}
.website-admin-tabs button{padding:9px 13px;border:0;border-bottom:2px solid transparent;background:transparent;color:#66737f;font-weight:700;cursor:pointer}
.website-admin-tabs button.active{border-bottom-color:var(--hms-primary);color:var(--hms-primary)}
.website-admin-panel{display:none}.website-admin-panel.active{display:block}
.admin-content-grid{margin-top:12px;display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:10px}
.website-content-card{overflow:hidden;border:1px solid var(--hms-border);border-radius:10px;background:#fff}
.website-content-card img,.content-image-placeholder{width:100%;height:150px;object-fit:cover}
.content-image-placeholder{display:grid;place-items:center;background:#eef6f4;color:var(--hms-primary);font-size:28px}
.website-content-card>div:not(.content-image-placeholder){padding:9px}
.website-content-card strong,.website-content-card small{display:block}
.website-content-card small{margin-top:3px;color:var(--hms-muted)}
.website-content-card form{padding:0 9px 9px}
.check-row{margin:10px 0!important;display:flex!important;align-items:center;gap:7px}
.check-row input{width:15px!important;height:15px!important;min-height:0!important}
.check-row-group{display:flex;gap:18px;flex-wrap:wrap}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('[data-web-tab]');
    const panels = document.querySelectorAll('[data-web-panel]');

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            tabs.forEach(item => item.classList.remove('active'));
            panels.forEach(item => item.classList.remove('active'));

            tab.classList.add('active');
            document.querySelector('[data-web-panel="' + tab.dataset.webTab + '"]')?.classList.add('active');
        });
    });
});
</script>
@endpush
