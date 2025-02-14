<!DOCTYPE html>
<html>
<head>
    <title>Color Details</title>
</head>
<body>
    <h1>Color Details</h1>

    <p><strong>Name:</strong> {{ $color->name }}</p>
    <p><strong>Hex Code:</strong> {{ $color->hex_code }}</p>
    <p><strong>RGB Code:</strong> {{ $color->rgb_code }}</p>
    <p><strong>CMYK Code:</strong> {{ $color->cmyk_code }}</p>

    <a href="{{ route('colors.index') }}">Back</a>
    <a href="{{ route('colors.edit', $color) }}">Edit</a>
</body>
</html>