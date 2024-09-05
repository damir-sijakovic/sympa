# Sympa

![Logo](logo.png)

Sympa is my project built with **Symfony PHP 6.4** and **Bootstrap 5.3**, featuring a dynamic webpage that includes a blog and an dashboard.

## Install

1. Run **check-requirements.sh**.
2. First drop in **createdb.sql** in **phpMyAdmin** to create database.
3. Get mariadb version: **$ mariadb --version** and add it to .env (this part: 10.6.18)
4. Run **reset.sh** to re-populate database.
5. (Optional) add **fake_data.sql** to **article** table.

## Version history

| Version | About |
|---------|-------|
| 0.1.8 | Login/Logout controllers and twig templates.<br/>Added "sort=id-asc, sort=id-desc" to ArticlesController::getArticlesPost method.<br/> |
| 0.1.5 | Added dashboard.<br/>Added "home" page.<br/>Added "about".<br/> |
| 0.1.0 | Basic project shape. |

## Note
The project is in the early stages of development but is still functional and usable.

## Licence
MIT
