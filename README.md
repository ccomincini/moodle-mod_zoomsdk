# Documentazione Zoom SDK Meeting (plugin Moodle)

## Funzionalità Complete
- Breakout rooms (placeholder UI)
- Sicurezza: obbligo password, sala d'attesa, join prima dell'organizzatore, autenticazione richiesta
- Media: video host/partecipanti, audio, mute, registrazione automatica
- Ricorrenza avanzata: giornaliera, settimanale, mensile, senza orario fisso, fine ricorrenza dinamica
- Salvataggio avanzato e trasmissione di tutti i parametri alle API Zoom SDK
- Stringhe in italiano e inglese aggiornate
- UX dinamica come mod_zoom

### Come funziona la finestra Zoom embedded
- L'iframe/div che ospita l'SDK Zoom è responsive.
- Alla pressione di "Entra nella riunione", l'altezza viene automaticamente ridimensionata a 700px e width 100%,
- Migliorato per essere adattivo al container (puoi customizzare via CSS/JS)

### Aggiornamento dinamico iframe/Zoom container
Nel file `view.php` puoi rendere la finestra embed di Zoom adattiva così:
```js
// Alla join della riunione
function resizeZoomContainer() {
   var root = document.getElementById('zmmtg-root');
   if (root) {
      root.style.width = '100%';
      root.style.height = (window.innerHeight * 0.85) + 'px';
   }
}
window.addEventListener('resize', resizeZoomContainer);
resizeZoomContainer(); // Primo caricamento
```
Aggiungi questo JS subito dopo aver attivato `zmmtg-root` nella view.

### Consigli di UX
- Mantenere i pulsanti grandi e ben visibili
- Controlla che la larghezza del container padre dell'iframe sia 100%
- Se Moodle ha header/fixed, sottrai via JS l'altezza per calcolare la finestra ottimale

### Note tecniche
- Tutte le opzioni di form, configurazione e meeting Zoom (se supportate dall'SDK) sono ora trasmesse
- I parametri di security/media sono coerenti con l'interfaccia UI e la documentazione Zoom
- La password accetta solo i caratteri validi Zoom (alfa, numeri, @ _ * - .)
- La ricorrenza supporta tutti i casi d’uso didattici

### TODO/Futuro
- Sincronizzazione/creazione stanza Breakout (quando SDK web lo supporta)
- Auto-adattività avanzata (altezza dinamica, modalità full screen per dispositivi mobili)
