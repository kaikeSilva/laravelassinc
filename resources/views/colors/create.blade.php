<!DOCTYPE html>
<html>
<head>
    <title>Create Color</title>
</head>
<body>
    <h1>Create New Color</h1>

    @if($errors->any())
        <div>
            <ul>
                @foreach($errors->all() as $error)
                    <li style="color: red;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('colors.store') }}">
        @csrf
        <div>
            <label>Name:</label>
            <input type="text" name="name" value="{{ old('name') }}" required>
        </div>
        <div>
            <label>Hex Code:</label>
            <input type="text" name="hex_code" value="{{ old('hex_code') }}" required>
        </div>
        <div>
            <label>RGB Code:</label>
            <input type="text" name="rgb_code" value="{{ old('rgb_code') }}" required>
        </div>
        <div>
            <label>CMYK Code:</label>
            <input type="text" name="cmyk_code" value="{{ old('cmyk_code') }}" required>
        </div>
        <button type="submit">Submit</button>
    </form>

    <a href="{{ route('colors.index') }}">Back</a>
</body>
</html>