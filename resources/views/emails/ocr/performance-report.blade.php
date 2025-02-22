@component('mail::message')
# {{ $report['title'] }}

@foreach ($report['sections'] as $section)
## {{ $section['heading'] }}

@foreach ($section['items'] as $item)
* {{ $item }}
@endforeach

@endforeach

@component('mail::button', ['url' => route('ocr.dashboard')])
Voir le Tableau de Bord OCR
@endcomponent

Merci,<br>
{{ config('app.name') }}
@endcomponent