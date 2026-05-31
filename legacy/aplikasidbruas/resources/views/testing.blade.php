<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Nyoba nyoba</title>
</head>
<body>
    <ul>
        @foreach ($menu['menus'] as $menu)        
            <li>{{ $menu['name'] }}</li>   
                @if ($menu['submenu'] != null)
                    <ul>
                        @foreach ($menu['submenu'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                @endif     
        @endforeach
    </ul>

</body>
</html>