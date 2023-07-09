const puppeteer = require('puppeteer');
const mysql = require('mysql2');


async function update(){
    // Параметры подключения к базе данных
    const connection = mysql.createConnection({
        host: process.env.DB_HOST,
        user: process.env.DB_PASSWORD,
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
        const selectQuery = "SELECT * FROM origami_product where provider='royal' and description = ''";
        connection.query(selectQuery, (err, rows) => {
            if (err) {
                console.error('Ошибка чтения данных: ', err);
                return;
            }
            if (rows.length > 0) {
                rows.forEach((row) => {
                    const id = row.id;
                    const url = row.productUrl;
                    const divContent = scrapeHTMLFromURL(url);
                    const updateQuery = `UPDATE origami_product SET description='${divContent}' WHERE id=${id}`;
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
}

async function scrapeHTMLFromURL(url) {
    // Launch a new browser instance
    const browser = await puppeteer.launch({
            headless: false,
            args: ['--no-sandbox'],
            downloadsPath: '/tmp'
        }
    );

    // Create a new page
    const page = await browser.newPage();

    // Navigate to the URL
    await page.goto(url);

    // Get the HTML content of the page
    const htmlContent = await page.content();

    // Find the desired div element
    const divSelector = 'div[itemprop="description"]';
    const divElement = await page.$(divSelector);

    let divContent = '';
    if (divElement) {
        // Get the innerHTML of the div element
        divContent = await page.evaluate(element => element.innerHTML, divElement);
        console.log(divContent);
    } else {
        console.log('Div not found.');
    }
    // Close the browser
    await browser.close();

    return divContent;
}

update().then(r => console.log('Done'));
