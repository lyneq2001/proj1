# Aplikacja wynajmu mieszkań

Prosty projekt w PHP umożliwiający dodawanie i wyszukiwanie ogłoszeń dotyczących nieruchomości.

## Kluczowe funkcje

- **System powiadomień e-mail** – automatyczne wysyłanie informacji do właścicieli ofert o nowych wiadomościach oraz zmianach statusu ogłoszeń. W przypadku braku konfiguracji serwera pocztowego wiadomości trafiają do katalogu `app/sent_emails/`.
- **Panel statystyk** – bogatszy widok dla administratorów i użytkowników prezentujący liczbę aktywnych ofert, polubień, nowych użytkowników i oczekujących zgłoszeń.
- **Integracja z mapą** – wizualizacja lokalizacji ofert przy pomocy Leaflet oraz obliczanie przybliżonej odległości do punktów POI (m.in. sklepów czy przystanków). Dane mapowe pobierane są z serwisu unpkg.com, dlatego w środowisku produkcyjnym wymagany jest dostęp do internetu.
- **Zaawansowana moderacja treści** – możliwość raportowania ofert przez użytkowników oraz zarządzania zgłoszeniami i statusami ogłoszeń przez administratorów.

## Wymagania i wskazówki

- Baza danych musi umożliwiać tworzenie nowych tabel oraz kolumn, ponieważ aplikacja automatycznie aktualizuje strukturę (`reports`, `offers.status`, opcjonalne `offer_status_history`).
- Skonfiguruj zmienne połączenia z bazą w pliku `app/config.php` przed uruchomieniem aplikacji.
- Funkcja `mail()` powinna być dostępna w środowisku serwerowym; w przeciwnym razie system zachowuje treści wysłanych powiadomień w katalogu `app/sent_emails/`.
- Aby włączyć przetwarzanie map, upewnij się, że PHP ma dostęp do funkcji `file_get_contents()` dla zapytań HTTP wykorzystywanych w `app/geocode_offers.php`.
