BEGIN:VEVENT
DSTAMP{{ $created_at }}
DTSTART{{ $starts_at }}
DTEND{{ $ends_at }}
SUMMARY:{{ $event_name }}
DESCRIPTION:{{ $description }}
@if ($organizer_name and $organizer_email)
ORGANIZER;CN:{{ $organizer_name }}:mailto:{{ $organizer_email }}
@endif
@if (!empty($frequency) and $frequency != 'NONE')
RRULE:FREQ={{ $frequency }}
@endif
URL:{{ $website }}
GEO:{{ $latitude }}:{{ $longitude }}
LOCATION:{{ $location }}
END:VEVENT