<?php

return [
    "accepted" => ":attribute muss akzeptiert werden.",
    "active_url" => ":attribute ist keine gültige Internet-Adresse.",
    "after" => ":attribute muss ein Datum nach dem :date sein.",
    "after_or_equal" => ":attribute muss ein Datum nach dem :date oder gleich dem :date sein.",
    "alpha" => ":attribute darf nur aus Buchstaben bestehen.",
    "alpha_dash" => ":attribute darf nur aus Buchstaben, Zahlen, Binde- und Unterstrichen bestehen.",
    "alpha_num" => ":attribute darf nur aus Buchstaben und Zahlen bestehen.",
    "array" => ":attribute muss ein Bereich sein.",
    "attached" => ":attribute ist bereits angehängt.",
    "attributes" => [
        "city" => "Stadt",
        "email" => "E-Mail-Adresse",
        "given_names" => "Vornamen",
        "groupId" => "Gruppe",
        "group_id" => "Gruppe",
        "groups_id" => "Gruppen",
        "house_no" => "Hausnummer",
        "last_name" => "Nachname",
        "name" => "Name",
        "organization_id" => "Einrichtung",
        "parent_email" => "Email der Eltern",
        "password" => "Passwort",
        "role" => "Rolle",
        "street" => "Straße",
        "title" => "Titel",
        "userId" => "Benutzer ID",
        "zip_code" => "Postleitzahl"
    ],
    "before" => ":attribute muss ein Datum vor dem :date sein.",
    "before_or_equal" => ":attribute muss ein Datum vor dem :date oder gleich dem :date sein.",
    "between" => [
        "array" => ":attribute muss zwischen :min & :max Elemente haben.",
        "file" => ":attribute muss zwischen :min & :max Kilobytes groß sein.",
        "numeric" => ":attribute muss zwischen :min & :max liegen.",
        "string" => ":attribute muss zwischen :min & :max Zeichen lang sein."
    ],
    "boolean" => ":attribute muss entweder 'true' oder 'false' sein.",
    "confirmed" => ":attribute stimmt nicht mit der Bestätigung überein.",
    "current_password" => "Das Passwort ist falsch.",
    "custom" => [
        "attribute-name" => [
            "rule-name" => "benutzerdefinierte Nachricht"
        ]
    ],
    "date" => ":attribute muss ein gültiges Datum sein.",
    "date_equals" => ":attribute muss ein Datum gleich :date sein.",
    "date_format" => ":attribute entspricht nicht dem gültigen Format für :format.",
    "different" => ":attribute und :other müssen sich unterscheiden.",
    "digits" => ":attribute muss :digits Stellen haben.",
    "digits_between" => ":attribute muss zwischen :min und :max Stellen haben.",
    "dimensions" => ":attribute hat ungültige Bildabmessungen.",
    "distinct" => ":attribute beinhaltet einen bereits vorhandenen Wert.",
    "email" => ":attribute muss eine gültige E-Mail-Adresse sein.",
    "ends_with" => ":attribute muss eine der folgenden Endungen aufweisen: :values",
    "exists" => "Der gewählte Wert für :attribute ist ungültig.",
    "file" => ":attribute muss eine Datei sein.",
    "filled" => ":attribute muss ausgefüllt sein.",
    "gt" => [
        "array" => ":attribute muss mehr als :value Elemente haben.",
        "file" => ":attribute muss größer als :value Kilobytes sein.",
        "numeric" => ":attribute muss größer als :value sein.",
        "string" => ":attribute muss länger als :value Zeichen sein."
    ],
    "gte" => [
        "array" => ":attribute muss mindestens :value Elemente haben.",
        "file" => ":attribute muss größer oder gleich :value Kilobytes sein.",
        "numeric" => ":attribute muss größer oder gleich :value sein.",
        "string" => ":attribute muss mindestens :value Zeichen lang sein."
    ],
    "image" => ":attribute muss ein Bild sein.",
    "in" => "Der gewählte Wert für :attribute ist ungültig.",
    "in_array" => "Der gewählte Wert für :attribute kommt nicht in :other vor.",
    "integer" => ":attribute muss eine ganze Zahl sein.",
    "ip" => ":attribute muss eine gültige IP-Adresse sein.",
    "ipv4" => ":attribute muss eine gültige IPv4-Adresse sein.",
    "ipv6" => ":attribute muss eine gültige IPv6-Adresse sein.",
    "json" => ":attribute muss ein gültiger JSON-String sein.",
    "lt" => [
        "array" => ":attribute muss weniger als :value Elemente haben.",
        "file" => ":attribute muss kleiner als :value Kilobytes sein.",
        "numeric" => ":attribute muss kleiner als :value sein.",
        "string" => ":attribute muss kürzer als :value Zeichen sein."
    ],
    "lte" => [
        "array" => ":attribute darf maximal :value Elemente haben.",
        "file" => ":attribute muss kleiner oder gleich :value Kilobytes sein.",
        "numeric" => ":attribute muss kleiner oder gleich :value sein.",
        "string" => ":attribute darf maximal :value Zeichen lang sein."
    ],
    "max" => [
        "array" => ":attribute darf maximal :max Elemente haben.",
        "file" => ":attribute darf maximal :max Kilobytes groß sein.",
        "numeric" => ":attribute darf maximal :max sein.",
        "string" => ":attribute darf maximal :max Zeichen haben."
    ],
    "mimes" => ":attribute muss den Dateityp :values haben.",
    "mimetypes" => ":attribute muss den Dateityp :values haben.",
    "min" => [
        "array" => ":attribute muss mindestens :min Elemente haben.",
        "file" => ":attribute muss mindestens :min Kilobytes groß sein.",
        "numeric" => ":attribute muss mindestens :min sein.",
        "string" => ":attribute muss mindestens :min Zeichen lang sein."
    ],
    "multiple_of" => ":attribute muss ein Vielfaches von :value sein.",
    "not_in" => "Der gewählte Wert für :attribute ist ungültig.",
    "not_regex" => ":attribute hat ein ungültiges Format.",
    "numeric" => ":attribute muss eine Zahl sein.",
    "password" => "Das Passwort ist falsch.",
    "passwords_validator" => [
        "data_leak" => "The given :attribute has appeared in a data leak. Please choose a different :attribute.",
        "letters" => "The :attribute must contain at least one letter.",
        "mixed_cases_lowercase" => "The :attribute must contain at least one lowercase letter.",
        "mixed_cases_uppercase" => "The :attribute must contain at least one uppercase letter.",
        "numbers" => "The :attribute must contain at least one number.",
        "symbols" => "The :attribute must contain at least one special characters."
    ],
    "present" => ":attribute muss vorhanden sein.",
    "prohibited" => ":attribute ist unzulässig.",
    "prohibited_if" => ":attribute ist unzulässig, wenn :other :value ist.",
    "prohibited_unless" => ":attribute ist unzulässig, wenn :other nicht :values ist.",
    "regex" => ":attribute Format ist ungültig.",
    "relatable" => ":attribute kann nicht mit dieser Ressource verbunden werden.",
    "required" => ":attribute muss ausgefüllt werden.",
    "required_if" => ":attribute muss ausgefüllt werden, wenn :other den Wert :value hat.",
    "required_unless" => ":attribute muss ausgefüllt werden, wenn :other nicht den Wert :values hat.",
    "required_with" => ":attribute muss ausgefüllt werden, wenn :values ausgefüllt wurde.",
    "required_with_all" => ":attribute muss ausgefüllt werden, wenn :values ausgefüllt wurde.",
    "required_without" => ":attribute muss ausgefüllt werden, wenn :values nicht ausgefüllt wurde.",
    "required_without_all" => ":attribute muss ausgefüllt werden, wenn keines der Felder :values ausgefüllt wurde.",
    "same" => ":attribute und :other müssen übereinstimmen.",
    "size" => [
        "array" => ":attribute muss genau :size Elemente haben.",
        "file" => ":attribute muss :size Kilobyte groß sein.",
        "numeric" => ":attribute muss gleich :size sein.",
        "string" => ":attribute muss :size Zeichen lang sein."
    ],
    "starts_with" => ":attribute muss mit einem der folgenden Anfänge aufweisen: :values",
    "string" => ":attribute muss ein String sein.",
    "timezone" => ":attribute muss eine gültige Zeitzone sein.",
    "unique" => ":attribute ist bereits vergeben.",
    "uploaded" => ":attribute konnte nicht hochgeladen werden.",
    "url" => ":attribute muss eine URL sein.",
    "uuid" => ":attribute muss ein UUID sein."
];