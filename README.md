# docker-mysql-php

Guida didattica per avviare e usare il progetto che contiene un web server PHP (Apache), un database MySQL e phpMyAdmin, orchestrati con Docker Compose.

Questa repository contiene un semplice esempio di applicazione PHP che si connette a MySQL. Lo scopo del README è spiegare passo-passo come avviare l'ambiente con Docker, quali file sono inclusi e come importare i file SQL forniti.

## Contenuto del progetto

- `docker-compose.yaml` - definisce 3 servizi: `web` (PHP+Apache), `db` (MySQL) e `phpmyadmin`.
- `Dockerfile` - immagine personalizzata per il servizio `web` (PHP/Apache).
- `users_create.sql` - script SQL (es. creazione tabelle) incluso nel repository.
- `users_insert.sql` - script SQL per popolare dati di esempio.
- `src/index.php` - semplice pagina PHP servita dal container web.

Aprire questi file se vuoi vedere come è configurata l'applicazione o personalizzare le credenziali/porte.

## Contratto minimo (inputs / outputs)

- Input: il codice sorgente in `src/` e gli script SQL.
- Output: un sito PHP raggiungibile da `http://localhost:8080` e phpMyAdmin su `http://localhost:8081`, con un database MySQL in esecuzione.

Error modes comuni: porte già occupate, permessi su file SQL, volume persistente MySQL che impedisce il re-inizializzo.

## Prerequisiti

- Docker Engine installato (versione moderna, preferibile >= 20.10).
- Docker Compose (integrato in Docker Desktop como `docker compose`) o il comando `docker-compose` disponibile.
- (Opzionale) client MySQL installato localmente per connessioni dirette.

Nota: su macOS è consigliato usare Docker Desktop. Verifica che Docker sia in esecuzione prima di procedere.

## Comandi passo-passo (macOS / zsh)

Di seguito trovi i comandi nell'ordine da eseguire per far partire l'ambiente. Esegui i comandi dalla radice del repository (dove si trova `docker-compose.yaml`).

1) Vai nella cartella del progetto:

```bash
cd /percorso/alla/cartella/docker-mysql-php
# es. cd ~/workspace/docker-mysql-php
```

2) Avvia i container (build + up in background)

# Se usi la nuova CLI integrata (consigliato):
```bash
docker compose up --build -d
```

# Oppure, se usi la vecchia CLI standalone:
```bash
docker-compose up --build -d
```

Questo comando costruisce l'immagine per il servizio `web` (usando il `Dockerfile`) e avvia tutti i servizi definiti.

3) Controlla lo stato dei container

```bash
docker compose ps
# oppure docker ps o docker-compose ps
```

4) Visualizza i log (in tempo reale)

```bash
docker compose logs -f
```

5) Apri l'applicazione nel browser

- Web app (PHP/Apache): http://localhost:8080
- phpMyAdmin (interfaccia web per MySQL): http://localhost:8081

Credenziali predefinite (definite in `docker-compose.yaml`):

- MySQL root: user `root` / password `rootpassword`
- Database creato: `myapp_db`
- Utente app: `myuser` / `mypassword`

Nota: queste sono credenziali di esempio solo per uso locale/didattico. Cambiale prima di usare in produzione.

6) Importare manualmente gli script SQL inclusi

MySQL nella official image esegue automaticamente gli script `.sql` solo se copiati nella directory di inizializzazione `/docker-entrypoint-initdb.d/` *al primo avvio* di un database nuovo (volume vuoto). Se vuoi importare manualmente i file `users_create.sql` e `users_insert.sql`, usa uno dei seguenti metodi.

- Metodo rapido (non interattivo) - copia e importa direttamente dal tuo host:

```bash
# Assicurati che il container sia in esecuzione
docker exec -i mysql-db mysql -u root -prootpassword myapp_db < users_create.sql
docker exec -i mysql-db mysql -u root -prootpassword myapp_db < users_insert.sql
```

- Alternativa: copiare nel container e poi importare (due passaggi):

```bash
docker cp users_create.sql mysql-db:/tmp/users_create.sql
docker exec -it mysql-db sh -c 'mysql -u root -prootpassword myapp_db < /tmp/users_create.sql'
```

Se il volume `db_data` è già popolato (es. hai avviato MySQL in precedenza), gli script di inizializzazione non verranno rieseguiti automaticamente. Per riinizializzare tutto (perdere i dati persistenti):

```bash
docker compose down -v
docker compose up --build -d
```

7) Accedere alla shell del container

- Entrare nella shell del container `web`:

```bash
docker compose exec web bash 2>/dev/null || docker compose exec web sh
```

- Entrare nella shell del container `db` (MySQL):

```bash
docker compose exec db sh
# dentro: mysql -u root -p
```

8) Fermare e rimuovere i container

```bash
docker compose down
# o per rimuovere anche i volumi (-> perderai i dati MySQL):
docker compose down -v
```

9) Forzare rebuild (se cambi Dockerfile o codice base):

```bash
docker compose build --no-cache
docker compose up -d
```

## Come è fatto il flusso di inizializzazione di MySQL

La Docker image `mysql:8.0` esegue automaticamente gli script copiati in `/docker-entrypoint-initdb.d/` soltanto quando il database è inizializzato per la prima volta (cioè se il volume dati è vuoto). Se hai bisogno che gli script SQL vengano eseguiti nuovamente, rimuovi il volume dei dati (`docker compose down -v`) e riavvia.

## Suggerimenti e risoluzione problemi

- Porta occupata: se `8080` o `8081` sono già in uso, modifica il mapping porte in `docker-compose.yaml`.
- Errori di permessi su file SQL: assicurati che i file `.sql` siano leggibili dall'utente (es. chmod a+r users_*.sql).
- Credenziali: non usare password in chiaro in produzione; usa variabili d'ambiente sicure o segreti.
- Se la build fallisce, guarda i log del servizio con `docker compose logs web`.

## File utili per esplorare

- `docker-compose.yaml` — servizi, porte e volumi.
- `Dockerfile` — immagine di `web` (PHP/Apache).
- `src/index.php` — punto di ingresso dell'applicazione PHP.
- `users_create.sql`, `users_insert.sql` — script per creare/popolare tabelle.

## Note finali

Questo progetto è pensato per scopi didattici: mostra come mettere insieme PHP, MySQL e phpMyAdmin usando Docker Compose. Sentiti libero di modificare le porte, le credenziali e gli script SQL per sperimentare.
