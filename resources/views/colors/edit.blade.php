<!DOCTYPE html>
<html>
<head>
    <title>Edit Color</title>
</head>
<body>
    <h1>Edit Color</h1>

    @if($errors->any())
        <div>
            <ul>
                @foreach($errors->all() as $error)
                    <li style="color: red;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('colors.update', $color) }}">
        @csrf
        @method('PUT')
        <div>
            <label>Name:</label>
            <input type="text" name="name" value="{{ old('name', $color->name) }}" required>
        </div>
        <div>
            <label>Hex Code:</label>
            <input type="text" name="hex_code" value="{{ old('hex_code', $color->hex_code) }}" required>
        </div>
        <div>
            <label>RGB Code:</label>
            <input type="text" name="rgb_code" value="{{ old('rgb_code', $color->rgb_code) }}" required>
        </div>
        <div>
            <label>CMYK Code:</label>
            <input type="text" name="cmyk_code" value="{{ old('cmyk_code', $color->cmyk_code) }}" required>
        </div>
        <button type="submit">Update</button>
    </form>

    <a href="{{ route('colors.index') }}">Back</a>
</body>
</html>