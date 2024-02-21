# 環境構築(所要時間3分)

zipファイル「ポートフォリオ」を解凍してから作業してください

cd easymarket-backend

//コンテナが一括で立ち上がります

./vendor/bin/sail up

//別のターミナルで実行します

cd easymarket-frontend

npm run dev

ここでページが表示

http://localhost:3000/

## 会員登録
会員仮登録メールはmailpitで確認します

http://localhost:3000/

## DB
PHPMyAdmin
http://localhost:8085/

ユーザー名：sail

パスワード: password

## 仕様
フロント：next.js

バックエンド: laravel

TDD(テスト駆動開発)でAPIでデータをやり取りしています。

##　実装機能

会員登録、認証

商品登録

商品購入(stripeのモックのため実際は会計しません)

マイページ
