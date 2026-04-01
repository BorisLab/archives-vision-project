# Guide d'installation ClamAV sur Windows

## Installation de ClamAV

### Méthode 1 : Avec Chocolatey (Recommandé)

1. **Installer Chocolatey** (si pas déjà installé)

   - Ouvrir PowerShell en Administrateur
   - Exécuter :

   ```powershell
   Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
   ```

2. **Installer ClamAV**
   ```powershell
   choco install clamav
   ```

### Méthode 2 : Installation manuelle

1. **Télécharger ClamAV**

   - Aller sur : https://www.clamav.net/downloads
   - Télécharger la version Windows (ex: clamav-1.3.0.win.x64.zip)
   - Extraire dans `C:\Program Files\ClamAV`

2. **Configuration initiale**

   ```powershell
   cd "C:\Program Files\ClamAV"

   # Copier les fichiers de configuration
   copy conf_examples\clamd.conf.sample conf_examples\clamd.conf
   copy conf_examples\freshclam.conf.sample conf_examples\freshclam.conf
   ```

3. **Éditer clamd.conf**

   - Ouvrir `conf_examples\clamd.conf` dans un éditeur
   - Commenter ou supprimer la ligne : `Example`
   - Modifier/ajouter les lignes :

   ```
   LogFile C:\Program Files\ClamAV\logs\clamd.log
   LogFileMaxSize 10M
   LogTime yes
   DatabaseDirectory C:\Program Files\ClamAV\database
   TCPSocket 3310
   TCPAddr 127.0.0.1
   MaxThreads 20
   MaxConnectionQueueLength 30
   ```

4. **Éditer freshclam.conf**

   - Ouvrir `conf_examples\freshclam.conf`
   - Commenter ou supprimer la ligne : `Example`
   - Modifier :

   ```
   DatabaseDirectory C:\Program Files\ClamAV\database
   UpdateLogFile C:\Program Files\ClamAV\logs\freshclam.log
   DatabaseMirror database.clamav.net
   ```

5. **Créer les dossiers nécessaires**

   ```powershell
   mkdir "C:\Program Files\ClamAV\database"
   mkdir "C:\Program Files\ClamAV\logs"
   ```

6. **Mettre à jour la base de signatures**
   ```powershell
   cd "C:\Program Files\ClamAV"
   .\freshclam.exe
   ```
   ⏱️ Cela peut prendre 5-10 minutes la première fois

## Démarrer ClamAV

### Option A : Mode service (Recommandé pour production)

1. **Créer un service Windows**

   ```powershell
   # PowerShell en Administrateur
   cd "C:\Program Files\ClamAV"

   # Installer comme service
   sc.exe create ClamAV binPath= "C:\Program Files\ClamAV\clamd.exe --config-file=C:\Program Files\ClamAV\conf_examples\clamd.conf" start= auto

   # Démarrer le service
   sc.exe start ClamAV
   ```

2. **Vérifier le statut**
   ```powershell
   sc.exe query ClamAV
   ```

### Option B : Mode manuel (Pour développement)

1. **Démarrer ClamAV daemon**

   ```powershell
   cd "C:\Program Files\ClamAV"
   .\clamd.exe --config-file="conf_examples\clamd.conf"
   ```

   💡 Laisser cette fenêtre PowerShell ouverte

2. **Dans un autre terminal**, tester la connexion
   ```powershell
   telnet localhost 3310
   ```
   Puis taper : `PING` → devrait répondre `PONG`

## Tester avec votre application Symfony

1. **Activer ClamAV dans .env**

   ```env
   CLAMAV_ENABLED=true
   CLAMAV_HOST=localhost
   CLAMAV_PORT=3310
   CLAMAV_TIMEOUT=30
   ```

2. **Vider le cache Symfony**

   ```bash
   php bin/console cache:clear
   ```

3. **Tester l'upload d'un fichier**

   - Uploader un fichier normal → devrait fonctionner
   - Le scan sera effectué automatiquement

4. **Tester avec un fichier de test EICAR** (fichier test standard pour antivirus)
   - Créer un fichier `test-virus.txt` avec ce contenu exact :
   ```
   X5O!P%@AP[4\PZX54(P^)7CC)7}$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!$H+H*
   ```
   - Essayer d'uploader ce fichier
   - ✅ ClamAV devrait le détecter comme "Eicar-Signature" et le bloquer

## Vérifier le fonctionnement dans les logs

1. **Logs ClamAV**

   ```powershell
   Get-Content "C:\Program Files\ClamAV\logs\clamd.log" -Tail 20
   ```

2. **Logs Symfony**
   ```bash
   tail -f var/log/dev.log
   ```
   - Vous verrez : "File scanned successfully" pour les fichiers propres
   - Vous verrez : "VIRUS DETECTED" pour les fichiers infectés

## Commandes utiles

### Mettre à jour les définitions de virus

```powershell
cd "C:\Program Files\ClamAV"
.\freshclam.exe
```

💡 À faire régulièrement (automatisable avec une tâche planifiée)

### Arrêter le service

```powershell
sc.exe stop ClamAV
```

### Redémarrer le service

```powershell
sc.exe stop ClamAV
sc.exe start ClamAV
```

### Scanner manuellement un fichier

```powershell
cd "C:\Program Files\ClamAV"
.\clamscan.exe "C:\chemin\vers\fichier.exe"
```

### Scanner un dossier entier

```powershell
.\clamscan.exe --recursive "C:\Users\alibo\Documents"
```

## Dépannage

### Problème : "Can't connect to clamd"

- Vérifier que `clamd.exe` est en cours d'exécution
- Vérifier le port 3310 :
  ```powershell
  netstat -an | findstr "3310"
  ```
- Devrait afficher : `TCP 127.0.0.1:3310 0.0.0.0:0 LISTENING`

### Problème : "Database not found"

- Exécuter `freshclam.exe` pour télécharger les signatures
- Vérifier que le dossier `database` contient des fichiers .cvd

### Problème : Erreur de permissions

- Exécuter PowerShell en Administrateur
- Vérifier les droits sur les dossiers ClamAV

## Configuration pour production

En production, vous devriez :

1. **Mettre à jour automatiquement** les définitions

   - Créer une tâche planifiée Windows pour `freshclam.exe`
   - Tous les jours à 3h du matin par exemple

2. **Monitorer le service**

   - Vérifier que `clamd` est toujours actif
   - Logger les scans dans un fichier dédié

3. **Ajuster les performances**
   - Dans `clamd.conf`, augmenter `MaxThreads` si beaucoup d'uploads
   - Ajuster `MaxConnectionQueueLength` selon le trafic

## Pour désactiver temporairement

Si vous voulez désactiver ClamAV sans le désinstaller :

```env
# Dans .env
CLAMAV_ENABLED=false
```

Puis vider le cache :

```bash
php bin/console cache:clear
```

L'application fonctionnera normalement sans scanner les fichiers.
