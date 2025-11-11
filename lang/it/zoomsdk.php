<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Stringhe in italiano.
 *
 * @package    mod_zoomsdk
 * @copyright  2025 Your Name <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Plugin.
$string['pluginname'] = 'Riunione Zoom SDK';
$string['modulename'] = 'Riunione Zoom SDK';
$string['modulenameplural'] = 'Riunioni Zoom SDK';
$string['modulename_help'] = 'Utilizza il modulo Riunione Zoom SDK per ospitare riunioni Zoom integrate direttamente in Moodle.';
$string['pluginadministration'] = 'Amministrazione Zoom SDK';

// Form.
$string['meetingname'] = 'Nome della riunione';
$string['meetingsettings'] = 'Impostazioni riunione';
$string['starttime'] = 'Ora di inizio';
$string['duration'] = 'Durata';
$string['meetingid'] = 'ID riunione';

// Meeting type.
$string['meetingtype'] = 'Tipo di riunione';
$string['meetingtype_help'] = 'Scegli il tipo di riunione da creare';
$string['type_instant'] = 'Riunione immediata';
$string['type_scheduled'] = 'Riunione programmata';
$string['type_recurring_fixed'] = 'Riunione ricorrente con orario fisso';
$string['type_recurring_nofixed'] = 'Riunione ricorrente senza orario fisso';

// Recurrence settings.
$string['recurrencesettings'] = 'Impostazioni ricorrenza';
$string['recurrence_type'] = 'Tipo di ricorrenza';
$string['recurrence_type_help'] = 'Con quale frequenza si ripete la riunione';
$string['recurrence_daily'] = 'Giornaliera';
$string['recurrence_weekly'] = 'Settimanale';
$string['recurrence_monthly'] = 'Mensile';
$string['repeat_interval'] = 'Ripeti ogni';
$string['repeat_interval_help'] = 'Numero di giorni/settimane/mesi tra le occorrenze';
$string['weekly_days'] = 'Ripeti il';
$string['weekly_days_help'] = 'Seleziona i giorni della settimana per la riunione';
$string['day_sunday'] = 'Domenica';
$string['day_monday'] = 'Lunedì';
$string['day_tuesday'] = 'Martedì';
$string['day_wednesday'] = 'Mercoledì';
$string['day_thursday'] = 'Giovedì';
$string['day_friday'] = 'Venerdì';
$string['day_saturday'] = 'Sabato';
$string['monthly_day'] = 'Giorno del mese';
$string['monthly_day_help'] = 'Giorno del mese (1-31)';
$string['monthly_week'] = 'Settimana del mese';
$string['monthly_week_help'] = 'Quale settimana del mese';
$string['monthly_week_day'] = 'Giorno della settimana';
$string['monthly_week_day_help'] = 'Quale giorno della settimana';
$string['week_last'] = 'Ultima';
$string['week_first'] = 'Prima';
$string['week_second'] = 'Seconda';
$string['week_third'] = 'Terza';
$string['week_fourth'] = 'Quarta';
$string['end_times'] = 'Numero di occorrenze';
$string['end_times_help'] = 'Quante volte si ripeterà la riunione (max 60)';
$string['end_date_time'] = 'Termina entro il';
$string['end_date_time_help'] = 'Data finale per le riunioni ricorrenti';

// Errori di validazione.
$string['starttime_past'] = 'L\'ora di inizio non può essere nel passato';
$string['duration_toolow'] = 'La durata deve essere di almeno 1 minuto';
$string['err_weekly_days'] = 'Devi selezionare almeno un giorno per la ricorrenza settimanale';
$string['err_end_times'] = 'Il numero di occorrenze deve essere compreso tra 1 e 60';
$string['err_repeat_interval'] = 'L\'intervallo di ripetizione deve essere almeno 1';
$string['err_monthly_day'] = 'Il giorno del mese deve essere compreso tra 1 e 31';

// View.
$string['joinmeeting'] = 'Partecipa alla Riunione (Integrata)';
$string['connecting'] = 'Connessione in corso...';

// Settings.
$string['sdkkey'] = 'SDK Key';
$string['sdkkey_desc'] = 'La tua Zoom Meeting SDK Key da marketplace.zoom.us';
$string['sdksecret'] = 'SDK Secret';
$string['sdksecret_desc'] = 'Il tuo Zoom Meeting SDK Secret (mantienilo riservato!)';

// Errors.
$string['zoomusernotfound'] = 'Utente Zoom non trovato per la tua email';
$string['failedtocreatemeeting'] = 'Impossibile creare la riunione Zoom';
$string['zoom_not_configured'] = 'L\'API Zoom non è configurata. Contatta l\'amministratore.';
$string['signaturefailed'] = 'Impossibile ottenere la firma Zoom';
$string['joinfailed'] = 'Impossibile accedere alla riunione';
$string['sdkinitfailed'] = 'Impossibile inizializzare Zoom SDK';
$string['apicallfailed'] = 'Chiamata API Zoom fallita';

// Privacy.
$string['privacy:metadata:zoomsdk_attendance'] = 'Informazioni sulla partecipazione degli utenti alle riunioni Zoom';
$string['privacy:metadata:zoomsdk_attendance:userid'] = 'ID utente';
$string['privacy:metadata:zoomsdk_attendance:jointime'] = 'Orario di accesso alla riunione';
$string['privacy:metadata:zoomsdk_attendance:leavetime'] = 'Orario di uscita dalla riunione';
$string['privacy:metadata:zoomsdk_attendance:duration'] = 'Durata della partecipazione in secondi';
$string['privacy:metadata:zoom'] = 'Dati utente inviati a Zoom per la partecipazione alla riunione';
$string['privacy:metadata:zoom:fullname'] = 'Nome completo visualizzato nella riunione';
$string['privacy:metadata:zoom:email'] = 'Indirizzo email per l\'identificazione';

// Capabilities.
$string['zoomsdk:addinstance'] = 'Aggiungere una nuova riunione Zoom SDK';
$string['zoomsdk:view'] = 'Visualizzare la riunione Zoom SDK';
$string['zoomsdk:viewattendance'] = 'Visualizzare le presenze alla riunione';

// Scheduled tasks.
$string['task_fetch_attendance'] = 'Recupera presenze da Zoom';
$string['task_cleanup_deleted'] = 'Pulisci moduli Zoom SDK eliminati';
