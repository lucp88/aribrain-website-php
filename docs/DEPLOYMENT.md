# Deployment van aribrain.eu — hoe het werkt

*Uitleg van het deploy-proces zoals het op 20 november 2026 is ingericht.*
*Dit document bevat geen wachtwoorden, tokens of accountnamen. Die staan in GitHub (Secrets) en op de server (`config/config.php`).*

---

## 1. In het kort

Een nieuwe versie van de website online zetten gaat zo:

1. Wijzigingen komen via een **pull request** op `main`.
2. Ga naar **GitHub → Actions → Deploy → Run workflow** (branch `main`).
3. Zit er een **database-wijziging** (migratie) in, dan pauzeert de deploy. Klik **Review deployments → Approve and deploy**.
4. Wacht tot alle stappen groen zijn. Klaar.

Mergen naar `main` zet nog niets online. Pas als jij de workflow start, gaat de nieuwe versie live. Zonder migraties hoef je niets goed te keuren.

---

## 2. Het grote plaatje

```
GitHub                          Vimexx-server
------                          -------------
repository (main)               domains/aribrain.eu/
  |                             |-- public_html/           = de website
  | jij: "Run workflow"         |   |-- config/config.php  (alleen op server)
  v                             |   |-- _deploy/           (alleen tijdens deploy)
GitHub Actions                  |   `-- .maintenance       (alleen tijdens migratie)
(tijdelijke Linux-machine)      |-- aribrain-backups/      = database-backups
  |                             `-- .aribrain-deploy-manifest
  |-- FTPS:  bestanden uploaden/verwijderen  -->  domains/aribrain.eu/
  `-- HTTPS: migratie-runner aanroepen       -->  public_html/_deploy/migrate.php
```

Er zijn twee soorten verkeer:

- **FTPS** (versleutelde FTP) voor het uploaden en verwijderen van bestanden. Dit loopt via een apart **deploy-FTP-account**, dat alleen bij `domains/aribrain.eu/` kan.
- **HTTPS** naar een kleine PHP-pagina, de *migratie-runner*. Die voert database-wijzigingen uit **op de server zelf**. GitHub kan niet rechtstreeks bij de database, en dat is goed: de database is alleen vanaf de server bereikbaar.

---

## 3. De vier stappen van de workflow

Het bestand `.github/workflows/deploy.yml` beschrijft de workflow. Het meeste werk doet het script `.github/scripts/deploy.sh`.

### Stap 1 — Check migrations (altijd)

1. De runner (`deploy/migrate.php`) en alle migraties (`sql/migrations/*.sql`) worden tijdelijk geüpload naar `public_html/_deploy/`.
2. GitHub vraagt aan de runner welke migraties nog niet zijn uitgevoerd.
3. Bovenaan de run-pagina verschijnt een samenvatting: *"No pending migrations"* of een lijst met migraties.

### Stap 2 — Migrate database (alleen als er migraties klaarstaan)

1. De workflow **pauzeert** en wacht op jouw goedkeuring. Dat is de omgeving `production` in GitHub, met jou als verplichte reviewer.
2. Na **Approve** gebeurt op de server, in deze volgorde:
   - **Onderhoudsmodus aan.** Het bestand `public_html/.maintenance` wordt aangemaakt, en bezoekers zien een nette pagina "We'll be right back".
   - **Volledige backup** van de database naar `domains/aribrain.eu/aribrain-backups/backup-<datum>-before-<migratie>.sql.gz`. Die map staat naast `public_html`, dus niemand kan hem via de website downloaden. Lukt de backup niet, dan wordt er niets gemigreerd.
   - **Migraties uitvoeren**, in volgorde. Elke geslaagde migratie wordt vastgelegd in de tabel `schema_migrations`, zodat hij nooit twee keer draait.
3. Als je **Reject** kiest, gebeurt er niets en blijft de site zoals hij was.

### Stap 3 — Deploy code

1. **Alleen gewijzigde bestanden** worden geüpload (zie §4).
2. Bestanden die uit de repo zijn verwijderd, worden ook van de server verwijderd. Dat geldt alleen voor bestanden die de deploy zelf ooit heeft geplaatst.
3. **Onderhoudsmodus uit.**
4. **Smoke test:** GitHub controleert of `https://aribrain.eu/index.php` antwoordt met HTTP 200.

### Stap 4 — Clean up (altijd, ook als er iets misging)

De tijdelijke map `_deploy/` met de runner wordt weer verwijderd. Buiten een deploy bestaat de runner dus niet op de server.

---

## 4. Hoe het uploaden werkt

De deploy houdt een **manifest** bij: een lijst van elk geüpload bestand met een vingerafdruk (SHA-256-hash). Dat manifest staat op de server in `domains/aribrain.eu/.aribrain-deploy-manifest`, buiten de website.

Bij elke deploy:

| Situatie | Wat er gebeurt |
|----------|----------------|
| Bestand is nieuw of gewijzigd in git | wordt geüpload |
| Bestand is ongewijzigd | wordt overgeslagen |
| Bestand is uit git verwijderd **en** staat in het manifest | wordt van de server verwijderd |
| Bestand staat alleen op de server (nooit door de deploy geplaatst) | **wordt nooit aangeraakt** |

**Wordt nooit geüpload en nooit verwijderd:**

| Pad | Waarom |
|-----|--------|
| `config/config.php` | instellingen en wachtwoorden van de server |
| `assets/images/team/` | teamfoto's die via het admin-paneel zijn geüpload |
| `.github/`, `deploy/`, `sql/`, `*.sql`, `docs/` | pipeline, migraties en interne documentatie |
| `setup-database.php`, `CLAUDE.md` | hoort niet op een live server |
| `.maintenance`, `.git*`, `.DS_Store` | technische bestanden |

Alleen bestanden die **in git staan** worden geüpload. Iets dat alleen lokaal op je laptop staat, komt dus nooit per ongeluk online.

> **Let op:** bewerk geen code direct op de server (FileZilla of File Manager). De deploy uploadt alleen bestanden die in git zijn veranderd, dus een handmatige wijziging blijft stilletjes staan totdat dat bestand in git weer verandert. Alleen `config/config.php` bewerk je wél op de server.

---

## 5. Database-wijzigingen (migraties)

Een migratie is een SQL-bestand dat een **bestaande** database aanpast, bijvoorbeeld door een kolom toe te voegen.

**Een migratie toevoegen:**

1. Maak `sql/migrations/NNN_korte_beschrijving.sql`, met NNN als volgnummer (`002_...`, `003_...`). Gebruik kleine letters, cijfers en underscores.
2. Schrijf gewone SQL-statements, gescheiden door `;`. Stored procedures (`DELIMITER`) worden niet ondersteund.
3. Werk ook `sql/schema.sql` bij met de eindsituatie. Dat bestand is voor een **nieuwe** installatie.
4. Zet de naam van de migratie onderaan `schema.sql` in de `INSERT INTO schema_migrations`, zodat een nieuwe installatie hem niet nog eens uitvoert.
5. Via een PR naar `main`, dan deployen. De deploy vraagt om je goedkeuring.

**Belangrijk:**

- Voer migraties **nooit met de hand** uit in phpMyAdmin. De pipeline weet dan niet dat ze gedraaid hebben en probeert het opnieuw. Heb je het toch gedaan, zie §6.
- MySQL kan tabelwijzigingen niet terugdraaien. Daarom maakt de deploy altijd eerst een backup. Houd migraties klein.

**Al uitgevoerd:** `001_merge_admins_into_users` (20 november 2026). Die migratie verhuisde alle beheerders van de oude tabel `admins` naar `users`. De oude tabel staat nog als `admins_pre_merge_backup` en kan weg zodra alles goed werkt.

---

## 6. Als er iets misgaat

Klik in GitHub op de rode stap om de foutmelding te zien.

| Rode stap | Wat het betekent | Wat te doen |
|-----------|------------------|-------------|
| **Check migrations** — "is not set" | een secret of variable ontbreekt | controleer §7 |
| **Check migrations** — `curl: (60)` of een SSL/TLS-melding | de FTP-server stuurt een ander certificaat | zie "Certificaat" hieronder |
| **Check migrations** — `530` of `Login` | het FTP-wachtwoord of de gebruikersnaam klopt niet | reset het wachtwoord van het deploy-account in DirectAdmin en werk het secret `FTP_PASSWORD` bij |
| **Check migrations** — `403` | de token in GitHub en op de server verschillen | zet in beide dezelfde `DEPLOY_TOKEN` |
| **Check migrations** — `503 DEPLOY_TOKEN is not configured` | de token ontbreekt in `config/config.php` op de server | voeg de regel toe (zie §7) |
| **Migrate database** | een migratie is mislukt; **de site staat in onderhoudsmodus** en de code is niet geüpload | zie "Migratie mislukt" hieronder |
| **Deploy code** | het uploaden is mislukt; de database is (eventueel) al bijgewerkt | los de oorzaak op en **draai de workflow opnieuw**: al uitgevoerde migraties worden overgeslagen |
| **Clean up** | `_deploy/` kon niet worden verwijderd | verwijder `public_html/_deploy/` met de hand in de File Manager |

### Migratie mislukt

De samenvatting van de run toont welke migratie en welk statement mislukten, plus de naam van de backup. De volledige MySQL-foutmelding staat **niet** in GitHub, omdat de repo publiek is. Hij staat op de server in `aribrain-backups/migrate-error-<datum>.log`.

1. Download de backup uit `domains/aribrain.eu/aribrain-backups/` (via de File Manager).
2. Ga naar **phpMyAdmin → Import** en kies dat backup-bestand. De database is dan weer zoals vóór de deploy.
3. Verwijder `public_html/.maintenance` via de File Manager. De site draait weer de oude versie.
4. Repareer de migratie in een nieuwe PR, merge, en deploy opnieuw.

### Site blijft "We'll be right back" tonen

Dan is er een deploy halverwege gestopt. Verwijder `public_html/.maintenance` om de onderhoudsmodus te beëindigen. Kijk eerst of de database in orde is (zie hierboven).

### Een migratie toch met de hand uitgevoerd

Laat de pipeline weten dat hij al gedaan is. Doe dit in phpMyAdmin (vervang de naam):

```sql
INSERT INTO schema_migrations (migration) VALUES ('002_korte_beschrijving');
```

### Certificaat

De FTP-server van Vimexx stuurt zijn certificaat (`*.zxcs.nl`, van Sectigo) **zonder de tussencertificaten** mee. De workflow vult die zelf aan uit `.github/certs/ftp-intermediates.pem`. Zo blijft de verbinding volledig gecontroleerd.

Stapt Vimexx ooit over op een andere certificaatleverancier, dan komt de foutmelding `curl: (60)` terug. De workflow toont dan zelf welk certificaat de server stuurt, en dat bestand moet worden vervangen. Zet nooit `FTP_PROTOCOL` op `ftp` om dit te omzeilen: dan gaat je FTP-wachtwoord onversleuteld over internet.

---

## 7. Instellingen (overzicht)

### In GitHub — *Settings → Secrets and variables → Actions*

| Soort | Naam | Inhoud |
|-------|------|--------|
| Secret | `FTP_SERVER` | servernaam van de Vimexx-hosting (zie DirectAdmin) |
| Secret | `FTP_USERNAME` | het deploy-FTP-account |
| Secret | `FTP_PASSWORD` | wachtwoord van dat account |
| Secret | `DEPLOY_TOKEN` | lange willekeurige code, dezelfde als op de server |
| Variable | `FTP_SERVER_DIR` | `public_html/` (relatief aan de startmap van het deploy-account) |
| Variable *(optioneel)* | `SITE_URL` | standaard `https://aribrain.eu` |
| Variable *(optioneel)* | `FTP_PROTOCOL` | standaard `ftps`; laat dit zo |
| Variable *(optioneel)* | `FTP_PORT` | standaard `21` |

### In GitHub — *Settings → Environments → production*

- **Required reviewers:** jij. Dit levert de "Approve"-knop bij migraties op.
- **Deployment branches:** alleen `main`.

### Op de server — `public_html/config/config.php`

```php
define('DEPLOY_TOKEN', '...');   // exact gelijk aan het GitHub-secret DEPLOY_TOKEN
```

### In DirectAdmin (Vimexx)

- aribrain.eu draait op het pakket **Webhosting Plus**. In het Vimexx-paneel staat het domein óók bij "Webhosting Nederland Plus", maar daar staat de website niet.
- Je komt in DirectAdmin via **Vimexx → pakket → Direct inloggen**, zonder wachtwoord.
- Het **deploy-FTP-account** is aangemaakt onder *FTP Management* (type *Domain*), met als startmap `domains/aribrain.eu/`.

### Token of wachtwoord vervangen

- **Deploy-token:** maak een nieuwe met `openssl rand -hex 32`, zet hem in `config/config.php` op de server, en daarna in het GitHub-secret `DEPLOY_TOKEN`.
- **FTP-wachtwoord:** reset het in DirectAdmin → FTP Management, en werk het GitHub-secret `FTP_PASSWORD` bij.

---

## 8. Waarom dit veilig is, ook met een publieke repo

- **Geen geheimen in de code.** Wachtwoorden en tokens staan versleuteld in GitHub Secrets en worden in logs automatisch verborgen.
- **Alleen jij kunt deployen**, alleen vanaf `main`, en alleen door de workflow zelf te starten. Pull requests van anderen starten nooit een deploy.
- **De migratie-runner bestaat alleen tijdens een deploy.** Ook dan werkt hij alleen met de geheime token, via POST, en toont hij nooit database-inhoud of serverpaden. De logs van een publieke repo zijn immers voor iedereen zichtbaar.
- **Backups blijven op de server**, buiten de website, en gaan nooit naar GitHub.
- **Alle FTP-verkeer is versleuteld** (FTPS), met volledige certificaatcontrole.
- **Het deploy-account kan alleen bij `domains/aribrain.eu/`**, niet bij je hele hosting en niet bij DirectAdmin. Lekt het ooit uit, reset dan alleen dat ene wachtwoord.
- **Externe GitHub-onderdelen zijn vastgezet op een exacte versie** (commit-SHA), zodat een kwaadaardige update niet ongemerkt binnenkomt.

---

## 9. Wat er op 20 november 2026 is gebeurd

| PR | Wat |
|----|-----|
| #2 | Opschoning: één accounttabel (`users`), werkende wachtwoord-reset, CSRF-bescherming, HTML-sanitizing, migratie 001 |
| #3 | Deze deploy-pipeline met de goedkeuringsstap voor migraties |
| #4 | Fix: de Vimexx-FTP-server stuurt geen tussencertificaten mee → `ftp-intermediates.pem` toegevoegd |
| #5 | Fix: de oorspronkelijke upload-action (Node) kreeg geen dataverbinding met Vimexx (`ECONNRESET`) → eigen upload met curl |
| #6 | Bijgewerkte gebruikershandleiding (ROI Analysis) |
| #7 | De Docs-pagina leest nu `assets/docs/USER_GUIDE.md` zelf in |

Eerste geslaagde deploy: migratie 001 uitgevoerd, 46 bestanden geüpload.

Daarna zijn met de hand verwijderd van de server: `setup-database.php`, `sql/`, `CLAUDE.md` en `includes/admin/auth.php` (oude bestanden die de deploy niet kende).

### Nog open

- De tabel `admins_pre_merge_backup` verwijderen in phpMyAdmin, zodra inloggen en alle gegevens goed blijken.
- `assets/docs/WEBSITE_ADMIN.md` (interne admin-documentatie) staat publiek op de website. Er staan geen geheimen in, maar het kan beter naar een map die niet wordt gedeployd, zoals `docs/`.
- Je hoofdwachtwoord van DirectAdmin/FTP is onbekend. Vraag het eventueel bij Vimexx op; voor de deploy is het niet nodig.

---

## 10. Bestanden die bij de deployment horen

| Bestand | Rol |
|---------|-----|
| `.github/workflows/deploy.yml` | de workflow (de vier stappen) |
| `.github/scripts/deploy.sh` | uploaden, verwijderen, runner aanroepen |
| `.github/certs/ftp-intermediates.pem` | ontbrekende tussencertificaten van de FTP-server |
| `deploy/migrate.php` | de migratie-runner (staat alleen tijdens een deploy op de server) |
| `includes/maintenance.php` | de pagina "We'll be right back" |
| `includes/bootstrap.php` | toont die pagina zolang `.maintenance` bestaat |
| `sql/migrations/` | de migraties |
| `sql/schema.sql` | volledige database voor een nieuwe installatie |
| `config/config.example.php` | voorbeeld van de server-instellingen, inclusief `DEPLOY_TOKEN` |
| `assets/docs/WEBSITE_ADMIN.md` | uitgebreide technische beheerhandleiding (Engels) |

### Handige links

- Deploy starten: https://github.com/lucp88/aribrain-website-php/actions/workflows/deploy.yml
- Secrets en variables: https://github.com/lucp88/aribrain-website-php/settings/secrets/actions
- Environment `production`: https://github.com/lucp88/aribrain-website-php/settings/environments
- Admin-login website: https://aribrain.eu/index.php?page=admin/login (inloggen met je **e-mailadres**)
