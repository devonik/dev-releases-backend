#!/bin/bash

{
  echo "APP_NAME='${ secrets.GITHUB_TOKEN }'"
  echo "APP_ENV='${ secrets.$APP_ENV }'"
  echo "APP_KEY='${ secrets.APP_KEY }'"
  echo "APP_URL='${ secrets.APP_URL }'"
  echo "APP_DEBUG='${ secrets.APP_DEBUG }'"
  echo "FILESYSTEM_DRIVER='${ secrets.FILESYSTEM_DRIVER }'"
  echo "FIREBASE_CREDENTIALS='${ secrets.FIREBASE_CREDENTIALS }'"
  echo "GOOGLE_APPLICATION_CREDENTIALS='${ secrets.GOOGLE_APPLICATION_CREDENTIALS }'"
  echo "DB_CONNECTION='${ secrets.DB_CONNECTION }'"
  echo "DB_DATABASE='${ secrets.DB_DATABASE }'"
  echo "DB_HOST='${ secrets.DB_HOST }'"
  echo "DB_PASSWORD='${ secrets.DB_PASSWORD }'"
  echo "DB_PORT='${ secrets.DB_PORT }'"
  echo "DB_USERNAME='${ secrets.DB_USERNAME }'"
} >> .env
