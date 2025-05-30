# coachtech 勤怠管理 制作中です

## 環境構築

### Docker ビルド

以下を実行します

1. ```
   git clone git@github.com:torch29/matsumoto-mogikadai1.git
   ```
2. docker desktop アプリを起動する

3. ```
   docker-compose up -d --build
   ```

### Laravel 環境構築

1. ```
   docker-compose exec php bash
   ```
2. ```
   composer install
   ```
3. `cp .env.example .env` を実行し、.env.example を .env にコピーする。
4. .env ファイルを開き、

   - `DB_HOST=127.0.0.1` を `DB_HOST=mysql` に変更する。
   - DB_DATABASE, DB_USERNAME, DB_PASSWORD を docker-compose.yml と合わせて任意に変更する。  
     （例）
     ```.env
     DB_DATABASE=laravel_db
     DB_USERNAME=laravel_user
     DB_PASSWORD=laravel_pass
     ```

5. ```
   php artisan key:generate
   ```
6. マイグレーションの実行

   ```
   php artisan migrate
   ```

7. シーディングの実行でダミーデータが作られます

   ```
   php artisan db:seed
   ```

8. 下記コマンドにて、シンボリックリンクの生成をお願いします。  
   public 下に storage ディレクトリが作成され参照します。

   ```
   php artisan storage:link
   ```

9. "The stream or file could not be opened"エラーが発生した場合  
   src ディレクトリにある storage ディレクトリに権限を設定

   ```
   chmod -R 777 storage
   ```

### MailHog の設定

勤怠管理アプリに会員登録する際にメールアドレス認証が必要となります。  
認証用のメールを確認するメールサーバーとして実装しています。

1. `docker-compose.yml`に、下記が設定されていることを確認します。

   ```yml
   mailhog:
     image: mailhog/mailhog
     ports:
       - "8025:8025"
       - "1025:1025"
     environment:
       MH_STORAGE: memory
   ```

2. もしも 1. の内容を修正した場合は Docker を再ビルドします。（MailHog のイメージをビルド）

   ```
   docker-compose up -d --build
   ```

3. `.env` ファイルを開き以下の項目を設定します。  
   MAIL_FROM_ADDRESS 欄は、適当なもので OK です。
   ```.env
   MAIL_DRIVER=smtp
   MAIL_HOST=mailhog
   MAIL_PORT=1025
   MAIL_USERNAME=
   MAIL_PASSWORD=
   MAIL_ENCRYPTION=
   MAIL_FROM_ADDRESS=mailhog@mailhog.com
   MAIL_FROM_NAME="${APP_NAME}"
   ```
4. http://localhost:8025 にアクセスして、送信されたメールを確認できます。  
   アプリ内では、会員登録後の「メール認証誘導画面」にあるボタンをクリックでも遷移できます。

### テストの準備と実行

PHPUnit によるテストを実行するための設定をします。

1. MySQL コンテナから、テスト用のデータベースを作成します。

   MySQL コンテナに入り root ユーザでログイン（要パスワード入力）

   ```
   docker-compose exec mysql bash
   ```

   ```
   $ mysql -u root -p
   ```

   ログインできたら、test データベースを作成します。（データベース名は任意です。）

   ```.mysql
   > CREATE DATABASE test;
   > SHOW DATABASES;

   ```

2. テスト用に.env ファイルを作成します。

   PHP コンテナに入り、下記を実行して .env をコピーした .env.testing を作成

   ```
   $ cp .env .env.testing
   ```

   `.env.testing` を開き、文頭の `APP_ENV` と `APP_KEY` を編集します。

   ```.env
   APP_NAME=Laravel
   APP_ENV=test
   APP_KEY=
   ```

   さらに、.env.testing にデータベースの接続情報を修正/記述します。

   ```.env
   DB_DATABASE=test
   DB_USERNAME=root
   DB_PASSWORD=root
   ```

3. アプリケーションキーの作成とマイグレーションを実行します

   ```
   $ php artisan key:generate --env=testing
   ```

   ```
   $ php artisan config:clear
   ```

   ```
   $ php artisan migrate --env=testing
   ```

4. テストの実行

   下記コマンドにて、登録されているテストが一括で実行されます

   ```
   $ php artisan test
   ```

## 使用技術

- PHP 7.4.9
- Laravel 8.83.8
- MySQL 8.0.26
- MailHog （会員登録時のメール確認用に使用）
- PHPUnit
- JavaScript
- nginx 1.21.1

## ER 図

```
ER 図は以下をご参照ください。
```

![ER図](ER.drawio.png)

## 使用方法

- トップページは、'/' です。
- 使用方法を追記します
-
- - インデントされた箇条書きリスト
  -

- シーディングにてダミーデータを作成すると、「テスト　ユーザー」という名前でログインすることが可能です。

  - テスト　ユーザーでログインすると、
  - テスト　ユーザーのログイン情報は以下の通りです。

    ```
     メールアドレス： test@example.com
     パスワード： 12345678
    ```

## URL

- 勤怠管理アプリのトップページ：http://localhost/
- phpMyAdmin：http://localhost:8080/
- MailHog：http://localhost:8025  
  （会員登録後のボタンクリックからも遷移できます）
