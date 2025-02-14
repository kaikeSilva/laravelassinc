<!DOCTYPE html>
<html>
  <head>
      <title>Colors List</title>
  </head>

  <body>
      <h1>Colors</h1>

      @if(session('success'))
          <p style="color: green;">{{ session('success') }}</p>
      @endif

      <a href="{{ route('colors.create') }}">Create New Color</a>
      <!-- Import Colors button -->
      <a href="{{ route('colors.importForm') }}" style="margin-left: 20px;">Import Colors</a>

      <table border="1" cellpadding="10">
          <thead>
              <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Hex Code</th>
                  <th>RGB Code</th>
                  <th>CMYK Code</th>
                  <th>Actions</th>
              </tr>
          </thead>
          <tbody>
              @foreach($colors as $color)
                  <tr>
                      <td>{{ $color->id }}</td>
                      <td>{{ $color->name }}</td>
                      <td>{{ $color->hex_code }}</td>
                      <td>{{ $color->rgb_code }}</td>
                      <td>{{ $color->cmyk_code }}</td>
                      <td>
                          <a href="{{ route('colors.show', $color) }}">View</a>
                          <a href="{{ route('colors.edit', $color) }}">Edit</a>
                          <form action="{{ route('colors.destroy', $color) }}" method="POST" style="display:inline;">
                              @csrf
                              @method('DELETE')
                              <button type="submit" onclick="return confirm('Are you sure?');">Delete</button>
                          </form>
                      </td>
                  </tr>
              @endforeach
          </tbody>
      </table>
  </body>
</html>