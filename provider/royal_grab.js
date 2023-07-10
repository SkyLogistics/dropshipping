const puppeteer = require('puppeteer');
const mysql = require('mysql2');
require('dotenv').config();

console.log(process.env.DB_USERNAME);
let headless = 'new';
if (process.env.ROYAL_ENV === 'local') {
    headless = false;
}
async function getUrl() { // Добавлено ключевое слово async
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
        const selectQuery = "SELECT * FROM origami_product where provider='royal' and description = ''";
        connection.query(selectQuery, async (err, rows) => { // Добавлено ключевое слово async
            if (err) {
                console.error('Ошибка чтения данных: ', err);
                return;
            }
            if (rows.length > 0) {
                for (const row of rows) { // Заменено на цикл for-of
                    const id = row.id;
                    const url = row.productUrl;

                    let urlUa = url.replace('royaltoys.com.ua/', 'royaltoys.com.ua/ua/');
                    const divContent = await scrapeHTMLFromURL(url, urlUa);
                    console.log('============== divContent and id = ' + id);
                    console.log(divContent[0]);
                    console.log(divContent[1]);
                    const updateQuery = `UPDATE origami_product
                                         SET description='${divContent[0]}',
                                             description_ua='${divContent[1]}'
                                         WHERE id = ${id}`;
                    connection.query(updateQuery, (err, result) => {
                        if (err) {
                            console.error(`Ошибка обновления поля для записи с ID ${id}: `, err);
                        } else {
                            console.log(`Поле успешно обновлено для записи с ID ${id}`);
                        }
                    });
                }
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

async function scrapeHTMLFromURL(url, urlUa) {
    const browser = await puppeteer.launch({
        headless: headless,
        args: ['--no-sandbox'],
        downloadsPath: '/tmp'
    });

    const page = await browser.newPage();
    await page.goto(url);
    const divSelector = 'div[itemprop="description"]';
    const divElement = await page.$(divSelector);


    let contentUa = '';
    let content = '';
    if (divElement) {
        content = await page.evaluate(element => element.innerHTML, divElement);
        await page.goto(urlUa);
        const divSelectorUa = 'div[itemprop="description"]';
        const divElementUa = await page.$(divSelectorUa);
        if (divElementUa) {
            contentUa = await page.evaluate(element => element.innerHTML, divElementUa);
        }

        await browser.close();
        return [content, contentUa];
    } else {
        console.log('Div not found.');
        await browser.close();
        return null;
    }
}

async function main() {
    await getUrl();
}

main().then(r => console.log('Done'));
