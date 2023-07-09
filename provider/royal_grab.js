const puppeteer = require('puppeteer');
const mysql = require('mysql2');
require('dotenv').config();

console.log(process.env.DB_USERNAME);
let content = '';

await function getUrl() {
    let myDesc = [];
    // Параметры подключения к базе данных
    const connection = mysql.createConnection({
        host: process.env.DB_HOST,
        user: process.env.DB_USERNAME,
        password: process.env.DB_PASSWORD,
        database: process.env.DB_DATABASE,
    });

    // Подключение к базе данных
    connection.connect((err) => {
        if (err) {
            console.error('Ошибка подключения: ', err);
            return;
        }

        console.log('Успешное подключение к базе данных MySQL');

        // Чтение данных из таблицы
        const selectQuery = "SELECT * FROM origami_product where provider='royal' and description = '' LIMIT 10";
        connection.query(selectQuery, (err, rows) => {
            if (err) {
                console.error('Ошибка чтения данных: ', err);
                return;
            }
            if (rows.length > 0) {
                rows.forEach((row) => {
                    const id = row.id;
                    const url = row.productUrl;
                    //console.log('ur = ' + url)
                    myDesc.push({"id": id, "url": url});
                    console.log(myDesc)
                    const divContent = scrapeHTMLFromURL(url);
                    console.log('divContent and id = ' + id)
                    console.log(divContent)
                    const updateQuery = `UPDATE origami_product
                                         SET description='${divContent}'
                                         WHERE id = ${id}`;
                    connection.query(updateQuery, (err, result) => {
                        if (err) {
                            console.error(`Ошибка обновления поля для записи с ID ${id}: `, err);
                        } else {
                            console.log(`Поле успешно обновлено для записи с ID ${id}`);
                        }
                    });
                });
            } else {
                console.log('Нет данных');
            }

            connection.end((err) => {
                if (err) {
                    console.error('Ошибка закрытия соединения: ', err);
                } else {
                    console.log('Соединение с базой данных закрыто');

                }
            });


        });
    });

    return myDesc;
}

async function scrapeHTMLFromURL(url) {
    const browser = await puppeteer.launch({
            headless: false,
            args: ['--no-sandbox'],
            downloadsPath: '/tmp'
        }
    );

    const page = await browser.newPage();
    await page.goto(url);
    const htmlContent = await page.content();
    const divSelector = 'div[itemprop="description"]';
    const divElement = await page.$(divSelector);

    if (divElement) {
        content = await page.evaluate(element => element.innerHTML, divElement);

    } else {
        console.log('Div not found.');
    }
    // Close the browser
    await browser.close();
}

let productUrls = getUrl();

console.log(productUrls);
