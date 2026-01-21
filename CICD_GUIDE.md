# CI/CD Guide (Beginner Friendly)

This guide explains **everything we did** to set up CI/CD for this PHP project and deploy it to **AWS EC2**.

---

## 1) What CI/CD means (in simple words)

- **CI (Continuous Integration)**: Every time you push code to GitHub, it is checked and prepared for deployment.
- **CD (Continuous Deployment)**: That new code is automatically sent to your server (EC2) without manual copying.

Result: **push to GitHub → automatically deployed to EC2**.

---

## 2) What you need before starting

- A **GitHub repository** containing this project.
- An **AWS EC2 server** running Ubuntu.
- SSH access to EC2 (a `.pem` key).

---

## 3) EC2 setup (one-time)

### 3.1 Install Apache + PHP
On your EC2:
```
sudo apt update
sudo apt install apache2 php libapache2-mod-php php-mysql unzip -y
```

### 3.2 Set Apache document root
We want Apache to serve from `/var/www/html/public`

```
sudo nano /etc/apache2/sites-available/000-default.conf
```

Set this line:
```
DocumentRoot /var/www/html/public
```

Restart Apache:
```
sudo systemctl restart apache2
```

---

## 4) Prepare GitHub Secrets (required)

GitHub Actions needs SSH access to EC2.

Go to:
**GitHub → Repo → Settings → Secrets and variables → Actions → New repository secret**

Add these secrets:

- `EC2_HOST` = `3.107.21.55`
- `EC2_USER` = `ubuntu`
- `EC2_SSH_KEY` = **your private key content**

### How to copy your PEM key:
On your Mac:
```
ssh-keygen -p -m PEM -f ~/web-host.pem
cat ~/web-host.pem | pbcopy
```
Then paste into `EC2_SSH_KEY`.

---

## 5) Create the CI/CD workflow

We added this file:
```
.github/workflows/deploy.yml
```

This workflow does:
1. Connect to EC2 using SSH
2. Upload the latest code to `/home/ubuntu/app/`
3. Use `sudo rsync` to copy into `/var/www/html/`
4. Restart Apache

---

## 6) Commit and push the workflow

```
git add .github/workflows/deploy.yml
git commit -m "Add CI/CD deploy workflow"
git push
```

After this, GitHub Actions runs automatically on every push to `main`.

---

## 7) How deployment now works

Any time you do:
```
git push
```

Your server updates automatically.

---

## 8) Common errors & quick fixes

### "Error loading key (stdin): error in libcrypto"
Your key format is wrong.
Fix:
```
ssh-keygen -p -m PEM -f ~/web-host.pem
```

### "rsync permission denied"
We fixed this by:
- Uploading first to `/home/ubuntu/app/`
- Then using `sudo rsync` into `/var/www/html/`

---

## 9) Optional: check deployment logs

GitHub:
- Go to **Actions** tab → click workflow → view logs.

Server (EC2):
```
sudo tail -n 50 /var/log/apache2/error.log
```

---

## 10) Summary (simple)

1. Push code to GitHub  
2. GitHub Actions auto-deploys to EC2  
3. Your site updates instantly  

---

If you want, I can also add:
- auto backups
- staging environment
- Slack / email deploy notifications
