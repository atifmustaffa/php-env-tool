
# 📦 `.env` Encryption Tool for PHP

A lightweight PHP CLI script to **encrypt and decrypt `.env` files** securely using AES-256-CBC.  
Supports multiple `.env` variants (e.g. `.env.local`, `.env.prod`), interactive key input, and automation flags.

---

## ✨ Features

- 🔐 AES-256 encryption using OpenSSL
- 🧠 Encrypt all or specific `.env` files
- 🔑 Decrypt via manual key or `ENV_KEY`
- ⚡ Supports flags:
  - `--generate`: generate a secure key automatically
  - `--force`: overwrite files without prompt
  - `--silent`: suppress all output
  - `--output=<file>`: specify output filename for single-file ops

---

## 🔁 Compatibility

This PHP encryption tool is fully compatible with the browser-based [JavaScript version](https://github.com/atifmustaffa/js-env-tool-web). Both tools use the same algorithm: AES-256-CBC with a SHA-256 hashed key and base64 encoding.

- You can encrypt using the PHP CLI tool and decrypt in the browser
- Or encrypt in the browser and decrypt using the PHP script

---

## 🚀 Usage

### ☁️ Option 1: Run via `curl`

```bash
curl -s https://raw.githubusercontent.com/atifmustaffa/php-env-tool/main/bin/php-env-tool.php | php -- encrypt --generate
```

Or decrypt:

```bash
curl -s https://raw.githubusercontent.com/atifmustaffa/php-env-tool/main/bin/php-env-tool.php | ENV_KEY=<yourkey> php -- decrypt .env.prod.enc
```

Or if there is any error, download it first and run:
```bash
curl -s -o php-env-tool.php https://raw.githubusercontent.com/atifmustaffa/php-env-tool/main/bin/php-env-tool.php
php php-env-tool.php encrypt --generate
```

---

### 📦 Option 2: Install globally via Composer

1. Clone the repo:
   ```bash
   git clone https://github.com/atifmustaffa/php-env-tool.git
   ```

2. Install globally:
   ```bash
   cd php-env-tool
   composer global config repositories.php-env-tool path $(pwd)
   composer global require atifmustaffa/php-env-tool
   ```

3. Use it from anywhere:
   ```bash
   php-env-tool encrypt --generate --silent
   ```

> ⚠️ Make sure to remember your key — there's no recovery!

---

## 🔐 Example Commands

| Task | Command |
|------|---------|
| Encrypt all `.env` files | `php php-env-tool.php encrypt` |
| Encrypt and auto-generate key | `php php-env-tool.php encrypt --generate` |
| Decrypt `.env.prod.enc` with ENV var | `ENV_KEY=<yourkey> php php-env-tool.php decrypt .env.prod.enc` |
| Encrypt quietly with overwrite | `php php-env-tool.php encrypt --force --silent` |
| Encrypt `.env.prod` and output to `.env` | `php env-tool.php encrypt .env.prod --output=.env` |
| Decrypt `.env.prod.enc` to `.env` | `php env-tool.php decrypt .env.prod.enc --output=.env` |

---

## 📝 Notes

- `.env.example` and `.env*.enc` are excluded during encryption
- Output files have `.enc` extension
- The script does **not** save or upload your key — it’s your responsibility to store it securely

---

## 📄 License

MIT License
