# AdWords Cost and Report Generator

## Description

This application generates random costs and campaign reports based on budget history data. It supports both console and file outputs and can handle CSV and JSON input formats.

## Requirements

- PHP 8.1 or higher
- Composer

## Installation

1. Install dependencies using Composer:
    ```sh
    composer install
    ```

## Usage

To run the application, use the following command:

```sh
php index.php [--start-date=Y-m-d] [--input=path/to/file(.csv|.json)] [--output=console|file] [--filepath=path/to/file] [--report=daily|detailed|both]
```

### Options

- `--start-date=Y-m-d`: The start date for the report in `Y-m-d` format. Defaults to `2019-01-01` if not provided.
- `--input=path/to/file(.csv|.json)`: The path to the input file containing budget history data. Supported formats are CSV and JSON. If no file is provided it will use some mock data.
- `--output=console|file`: The output type. Can be either `console` or `file`. Defaults to `console`.
- `--filepath=path/to/file`: The path to the output file. Required if `--output=file` is specified.
- `--report=daily|detailed|both`: The type of report to generate. Can be `daily`, `detailed`, or `both`. Note that `both` is only available with console output.

### Examples

1. Generates a daily and detailed report and output to console using mock data:
    ```sh
    php index.php
    ```

2. Generate a daily report and output to console:
    ```sh
    php index.php --start-date=2019-01-01 --input=example.csv --output=console --report=daily
    ```

3. Generate a detailed report and output to a file:
    ```sh
    php index.php --start-date=2019-01-01 --input=example.json --output=file --filepath=report.csv --report=detailed
    ```

4. Generate both reports and output to console:
    ```sh
    php index.php --start-date=2019-01-01 --input=example.csv --output=console --report=both
    ```
   
## Running as an API

To run the application as an API, use the following command:

```sh
php -S localhost:8080 -t public
```

### Endpoint
`POST /generate-costs`

### Request Body example
```json
{
   "startDate": "2019-01-01",
   "budgetHistory": {
      "2019-01-01": [
         {"time": "10:00", "amount": 7},
         {"time": "11:00", "amount": 0},
         {"time": "12:00", "amount": 1},
         {"time": "23:00", "amount": 6}
      ],
      "2019-01-05": [
         {"time": "10:00", "amount": 2}
      ],
      "2019-01-06": [
         {"time": "00:00", "amount": 0}
      ],
      "2019-01-09": [
         {"time": "13:13", "amount": 1}
      ],
      "2019-03-01": [
         {"time": "12:00", "amount": 0},
         {"time": "14:00", "amount": 1}
      ],
      "2019-03-22": [
         {"time": "14:00", "amount": 0}
      ]
   }
}
```
