<div class="col-md-6">
    <div class="form-group">
        <label for="name">Nama</label>
        <select name="mapping[name]" class="form-control" id="name">
            <option value="">-- Pilih Column --</option>
            @foreach($headings as $key => $value)
                <option value="{{ $value }}">{{ $value }}</option>
            @endforeach
        </select>
        <div data-error="name" class="invalid-fedback">
            <span class="text-danger" style="font-size: 0.8em"></span>
        </div>
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="email">Email</label>
        <select name="mapping[email]" class="form-control" id="email">
            <option value="">-- Pilih Column --</option>
            @foreach($headings as $key => $value)
                <option value="{{ $value }}">{{ $value }}</option>
            @endforeach
        </select>
        <div data-error="email" class="invalid-fedback">
            <span class="text-danger" style="font-size: 0.8em"></span>
        </div>
    </div>
</div>
<div class="col-md-12">
    <div class="form-group">
        <label for="phone">Phone</label>
        <select name="mapping[phone]" class="form-control" id="phone">
            <option value="">-- Pilih Column --</option>
            @foreach($headings as $key => $value)
                <option value="{{ $value }}">{{ $value }}</option>
            @endforeach
        </select>
        <div data-error="phone" class="invalid-fedback">
            <span class="text-danger" style="font-size: 0.8em"></span>
        </div>
    </div>
</div>
<div class="col-md-12">
    <div class="form-group">
        <label for="address">Address</label>
        <select name="mapping[address]" class="form-control" id="address">
            <option value="">-- Pilih Column --</option>
            @foreach($headings as $key => $value)
                <option value="{{ $value }}">{{ $value }}</option>
            @endforeach
        </select>
        <div data-error="address" class="invalid-fedback">
            <span class="text-danger" style="font-size: 0.8em"></span>
        </div>
    </div>
</div>
<input type="hidden" name="file_path" value="{{ $file }}">