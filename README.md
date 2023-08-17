<!-- PROJECT NAME -->
<div align="center">
  <h1 align="center">Media Stream Microservice</h1>
</div>
<br>
<br>
<!-- TABLE OF CONTENTS -->
<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#requirements">Requirements</a>
      <ul>
        <li><a href="#built-with">Built With</a></li>
      </ul>
    </li>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#prerequisites">Prerequisites</a></li>
        <li><a href="#installation">Installation</a></li>
      </ul>
    </li>
    <li><a href="#usage">Usage</a></li>
  </ol>
</details>

<br>

<!-- ABOUT THE PROJECT -->
## Requirements

Build a publicly accessible service designed to stream an audio MP3 file to users upon
accessing a specific URL corresponding to the desired audio episode. Certain episodes may
be set as private, requiring authentication for streaming, which can be achieved either
through header authentication or via a signed URL.


<br>


## Built With

<br>

* [![Laravel][Laravel.com]][Laravel-url]

<br>

<!-- GETTING STARTED -->
# Getting Started

## Prerequisites

Before running this project, ensure that you have the following installed:

* PHP (minimum version: 8.1.0)
* Composer (minimum version: 2.0.0)
* Node.js (minimum version: 14.0.0)
* NPM (minimum version: 6.0.0)
* MySQL (minimum version: 8.0.0)

## Installation

Clone the repository to your local machine:

```bash
git clone https://github.com/Ahmad-Chebbo/shopping-basket.git
```

Install the PHP dependencies:
```bash
composer install
```

Install the NPM packages:
```bash
npm install
```

Copy the .env.example file to a new file called .env:
```bash
cp .env.example .env
```

Generate an application key:
```vbnet
php artisan key:generate
```

Update the .env file with your database credentials.


## Run the migrations:


```bash
php artisan migrate --seed
```

## Running the application

To start the development server, run the following command:

```bash
php artisan serve
```  

And run the node development by running the followin command:

```bash
npm run dev
```

Then visit http://localhost:8000 to view the application.

Testing
To run the tests, run the following command:

```bash
php artisan test
```

This should run the tests and display the results.
I have created one test unit called CartTest that contains 3 functions

* Add item to basket
* Get the basket items
* Remove item from the basket

<br>

# Usage

* Access the application in your web browser at http://localhost:8000.

* Use the following credentials to access the dashboard:

    * Admin: admin@app.com, password: password
    * Sales Team: sales@app.com, password: password
* Add products to the shopping platform and test the shopping basket to ensure that the data on removed items is being captured correctly.
* Use the data on removed items page that can be accessed from the control panel to provide targeted discounts to customers and improve sales.

# NOTES

During the development process of this project, I created additional backend functionalities such as a checkout process. Although these features are not used in the current version, you are welcome to review the code.

In addition, I created an event listener that moves basket and basket items from before login to after login in order to maintain user sessions. This functionality allows users to add items to their cart and remove them before logging in. However, it is currently not being utilized in the project.






<!-- MARKDOWN LINKS & IMAGES -->
<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->
[contributors-shield]: https://img.shields.io/github/contributors/othneildrew/Best-README-Template.svg?style=for-the-badge
[contributors-url]: https://github.com/othneildrew/Best-README-Template/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/othneildrew/Best-README-Template.svg?style=for-the-badge
[forks-url]: https://github.com/othneildrew/Best-README-Template/network/members
[stars-shield]: https://img.shields.io/github/stars/othneildrew/Best-README-Template.svg?style=for-the-badge
[stars-url]: https://github.com/othneildrew/Best-README-Template/stargazers
[issues-shield]: https://img.shields.io/github/issues/othneildrew/Best-README-Template.svg?style=for-the-badge
[issues-url]: https://github.com/othneildrew/Best-README-Template/issues
[license-shield]: https://img.shields.io/github/license/othneildrew/Best-README-Template.svg?style=for-the-badge
[license-url]: https://github.com/othneildrew/Best-README-Template/blob/master/LICENSE.txt
[linkedin-shield]: https://img.shields.io/badge/-LinkedIn-black.svg?style=for-the-badge&logo=linkedin&colorB=555
[linkedin-url]: https://linkedin.com/in/othneildrew
[product-screenshot]: images/screenshot.png
[Next.js]: https://img.shields.io/badge/next.js-000000?style=for-the-badge&logo=nextdotjs&logoColor=white
[Next-url]: https://nextjs.org/
[React.js]: https://img.shields.io/badge/React-20232A?style=for-the-badge&logo=react&logoColor=61DAFB
[React-url]: https://reactjs.org/
[Vue.js]: https://img.shields.io/badge/Vue.js-35495E?style=for-the-badge&logo=vuedotjs&logoColor=4FC08D
[Vue-url]: https://vuejs.org/
[Angular.io]: https://img.shields.io/badge/Angular-DD0031?style=for-the-badge&logo=angular&logoColor=white
[Angular-url]: https://angular.io/
[Svelte.dev]: https://img.shields.io/badge/Svelte-4A4A55?style=for-the-badge&logo=svelte&logoColor=FF3E00
[Svelte-url]: https://svelte.dev/
[Laravel.com]: https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white
[Laravel-url]: https://laravel.com
[Bootstrap.com]: https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white
[Bootstrap-url]: https://getbootstrap.com
[JQuery.com]: https://img.shields.io/badge/jQuery-0769AD?style=for-the-badge&logo=jquery&logoColor=white
[JQuery-url]: https://jquery.com 
