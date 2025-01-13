# SMS Processing Service

## Table of contents

- [General Info](#general-info)
- [Requirements](#requirements)
- [Setup](#setup)
- [Usage](#usage)
- [Endpoints](#endpoints)

## General Info

Service for SMS Processing with AWS SQS and AWS SNS Integration

## Requirements

- [php ^8.1](https://www.php.net/ "PHP")

## Setup

- Clone the project and navigate to it's root path and install the required dependency packages using the below commands on the terminal/command line interface.

  ```bash
  git clone https://github.com/ilejohn-official/sms-processing-service.git
  cd sms-processing-service
  ```

  ``` composer install
  ```

- Copy and paste the content of the .env.example file into a new file named .env in the same directory as the former and set it's values based on your environment's configuration.

- Generate Application Key

  ``` php artisan key:generate
  ```

- Run Migration

  ``` php artisan migrate
  ```

- Ensure the php redis extension is installed and that redis is running

  ``` sudo apt-get install php-redis
  ```

- Ensure AWS is set up correctly with correct credentials

``` AWS_ACCESS_KEY_ID=
    AWS_SECRET_ACCESS_KEY=
    AWS_DEFAULT_REGION=us-east-1
    AWS_BUCKET=
    AWS_USE_PATH_STYLE_ENDPOINT=false
    AWS_SQS_QUEUE_URL=
 ```

## Usage

- To run local server

  ``` php artisan serve
  ```

- To run queue server

  ``` php artisan queue:work
  ```

- Run the polling command after AWS must have been correctly configured for the sms request processing

  ``` php artisan app:sqs-poll
  ```

## Endpoints

### **Transaction processing**

  Send a successful transaction (as in a webhook) with the `amount` as payload to this endpoint

#### `POST /api/transaction/success`
