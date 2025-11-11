# Zoom SDK Meeting Module for Moodle

Un modulo attività Moodle che integra meeting Zoom direttamente nei corsi utilizzando Zoom Meeting SDK.

## Caratteristiche

### Tipologie di Meeting

- **Meeting Immediato** (Type 1): Avvia un meeting istantaneamente
- **Meeting Programmato** (Type 2): Programma un meeting per una data e ora specifica
- **Meeting Ricorrente con Orario Fisso** (Type 3): Programma meeting ricorrenti con orario di inizio fisso
- **Meeting Ricorrente senza Orario Fisso** (Type 8): Crea meeting ricorrenti che possono essere avviati in qualsiasi momento

### Opzioni di Ricorrenza

#### Ricorrenza Giornaliera
- Ripeti ogni X giorni
- Imposta numero di occorrenze o data di fine

#### Ricorrenza Settimanale
- Ripeti ogni X settimane
- Seleziona giorni specifici della settimana (Domenica-Sabato)
- Imposta numero di occorrenze o data di fine

#### Ricorrenza Mensile
- **Per Giorno del Mese**: Ripeti in un giorno specifico (1-31)
- **Per Settimana del Mese**: Ripeti in una settimana e giorno specifici (es. "2° Martedì")
- Imposta numero di occorrenze o data di fine

### Funzionalità Aggiuntive

- Interfaccia Zoom SDK integrata
- Tracciamento automatico delle presenze
- Supporto per ruoli host e partecipante
- Generazione firma JWT per autenticazione sicura
- Conformità Privacy API di Moodle

## Requisiti

- Moodle 4.0 o superiore
- Plugin mod_zoom (per integrazione API Zoom)
- Credenziali Zoom Meeting SDK (SDK Key e SDK Secret)
- Credenziali Zoom API (Account ID, Client ID, Client Secret)

## Installazione

1. Clona o scarica questo repository in `{moodle}/mod/zoomsdk`
2. Visita Amministrazione del sito → Notifiche per installare
3. Configura le credenziali API Zoom nelle impostazioni del plugin mod_zoom
4. Configura le credenziali SDK Zoom nelle impostazioni del plugin mod_zoomsdk:
   - Impostazioni → Plugin → Moduli attività → Zoom SDK Meeting
   - Inserisci SDK Key e SDK Secret da [Zoom Marketplace](https://marketplace.zoom.us)

## Schema Database

### Tabella: `zoomsdk`

| Campo | Tipo | Descrizione |
|-------|------|-------------|
| id | int | Chiave primaria |
| course | int | ID corso |
| name | varchar(255) | Nome meeting |
| meeting_id | varchar(20) | ID meeting Zoom |
| host_id | varchar(100) | ID utente host Zoom |
| meeting_type | int | 1=immediato, 2=programmato, 3=ricorrente fisso, 8=ricorrente no fisso |
| start_time | int | Timestamp inizio (opzionale per type 8) |
| duration | int | Durata in secondi |
| password | varchar(50) | Password meeting |
| join_url | varchar(500) | URL partecipazione Zoom |
| recurrence_type | int | 1=giornaliera, 2=settimanale, 3=mensile |
| repeat_interval | int | Ripeti ogni X giorni/settimane/mesi |
| weekly_days | varchar(20) | Giorni separati da virgola (1-7) |
| monthly_day | int | Giorno del mese (1-31) |
| monthly_week | int | Settimana del mese (-1=ultima, 1-4) |
| monthly_week_day | int | Giorno della settimana per mensile (1-7) |
| end_times | int | Numero di occorrenze (max 60) |
| end_date_time | int | Timestamp data fine |

### Tabella: `zoomsdk_attendance`

| Campo | Tipo | Descrizione |
|-------|------|-------------|
| id | int | Chiave primaria |
| zoomsdkid | int | Riferimento a zoomsdk |
| userid | int | ID utente |
| jointime | int | Timestamp ingresso |
| leavetime | int | Timestamp uscita |
| duration | int | Durata in secondi |

## Note di Aggiornamento

### Versione 1.1.0 (2025111102)

- Aggiunto supporto per meeting ricorrenti
- Aggiunta selezione tipo di meeting
- Nuovi campi database per impostazioni ricorrenza
- Migrazione automatica per installazioni esistenti

## Utilizzo

### Creare un Meeting

1. Attiva modifica nel tuo corso
2. Aggiungi attività → Zoom SDK Meeting
3. Configura impostazioni meeting:
   - Inserisci nome meeting
   - Seleziona tipo di meeting
   - Per programmato/ricorrente: imposta orario inizio e durata
   - Per meeting ricorrenti: configura pattern di ricorrenza
4. Salva e visualizza

### Partecipare a un Meeting

1. Clicca sull'attività Zoom SDK Meeting
2. Clicca sul pulsante "Partecipa alla Riunione"
3. Lo Zoom SDK si caricherà e connetterà automaticamente

## Integrazione API

Questo modulo si integra con:
- **Zoom Meeting SDK** per interfaccia meeting integrata
- **Zoom REST API** (tramite mod_zoom) per gestione meeting

## Privacy

Il modulo implementa la Privacy API di Moodle e memorizza:
- Record presenze utenti (orari ingresso/uscita)
- Nome utente ed email inviati a Zoom durante i meeting

## Ambiente di Test

**Server**: https://fad.izsum.it  
**Architettura**: Docker Compose con 3 servizi (nginx, php-fpm, mariadb)  
**Path plugin**: `/media/dati/ivf/docker/izsumfad/var/www/moodle/mod/zoomsdk`

### Container
- `izsumfad-nginx-1` - Web server
- `izsumfad-php-1` - PHP-FPM
- `izsumfad-mariadb-1` - Database

## Sviluppo

### Struttura File Principali

```
mod/zoomsdk/
├── db/
│   ├── access.php          # Definizioni capabilities
│   ├── install.xml         # Schema database
│   ├── upgrade.php         # Script di aggiornamento
│   └── tasks.php          # Task schedulati
├── lang/
│   ├── en/zoomsdk.php     # Stringhe inglese
│   └── it/zoomsdk.php     # Stringhe italiano
├── classes/
│   ├── event/             # Eventi Moodle
│   ├── privacy/           # Provider privacy
│   └── task/              # Task schedulati
├── lib.php                # Funzioni core del modulo
├── locallib.php           # Funzioni interne
├── mod_form.php           # Form di configurazione
├── view.php               # Vista principale
├── generate_signature.php # Generazione JWT per SDK
└── version.php            # Informazioni versione
```

## Licenza

GNU GPL v3 o successive

## Crediti

Sviluppato per la piattaforma IZSUM e-Learning  
Provider ECM accreditato AGENAS n. 925

## Supporto

Per problemi e richieste di funzionalità, utilizzare il tracker GitHub.

## TODO / Roadmap

- [ ] Supporto per webinar
- [ ] Registrazione automatica meeting
- [ ] Report presenze avanzati
- [ ] Integrazione calendario Moodle
- [ ] Notifiche email pre-meeting
