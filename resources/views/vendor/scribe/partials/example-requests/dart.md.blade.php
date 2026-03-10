@php
    // Adding this so we get IDE code completion for $endpoint
    /** @var \Knuckles\Camel\Output\OutputEndpointData $endpoint */
@endphp
```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

void main() async {
var url = Uri.parse('{{ $baseUrl }}/{{ $endpoint->boundUri }}');

@if (!empty($endpoint->cleanBodyParameters))
    var body = jsonEncode(@json($endpoint->cleanBodyParameters));
@endif

@if (!empty($endpoint->headers))
    var headers = {
    @foreach ($endpoint->headers as $header => $value)
        "{{ $header }}": "{{ $value }}",
    @endforeach
    };
@endif

var response = await http.{{ strtolower($endpoint->httpMethods[0]) }}(url,
@if (!empty($endpoint->cleanBodyParameters))
    body: body,
@endif
@if (!empty($endpoint->headers))
    headers: headers,
@endif
);

print('Response status: ${response.statusCode}');
print('Response body: ${response.body}');
}
