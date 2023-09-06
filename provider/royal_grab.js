const puppeteer = require('puppeteer');
const mysql = require('mysql2');
const cheerio = require('cheerio');
require('dotenv').config();

console.log(process.env.DB_USERNAME);
let headless = 'new';
if (process.env.ROYAL_ENV === 'local') {
    //headless = false;
}

async function parseTableData(html, lang) {
    const $ = cheerio.load(html);
    const tableRows = $('.product_features-item');

    //const tableData = {};
    let tableArray = [];
    tableRows.each((index, row) => {
        const title = $(row).find('.product_features-title span').text();
        const value = $(row).find('.product_features-value').text();
        tableArray.push({'lang':lang, 'title':title,'value':value});
    });

    console.log(JSON.stringify(tableArray));

    //return '';

    return JSON.stringify(tableArray);
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
        const selectQuery = "SELECT * FROM products where provider='royal' and options is null ";
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

                    const propertiesParsed = await parseTableData(divContent[2], 'ru');
                    const propertiesParsedUa = await parseTableData(divContent[3], 'ua');
                    console.log(JSON.stringify(propertiesParsed));
                    console.log(JSON.stringify(propertiesParsedUa));

                    const updateQuery = 'UPDATE origami_product SET ' +
                        "options = " + `?,` +
                        "options_ua = " + `?,` +
                        "properties = " + `?,` +
                        "properties_ua = " + `?,` +
                        " WHERE id = ?";
                    const values = [propertiesParsed, propertiesParsedUa, divContent[2], divContent[3], id];
                    connection.query(updateQuery, values, (err, result) => {
                        if (err) {
                            console.error(`Ошибка обновления поля для записи с ID ${id}: `, err);
                            process.exit(1);
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
                    process.exit(1);
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

    const divProperties = 'div[id="product-options"]';
    const divElementProp = await page.$(divProperties);

    let content = '';
    let contentUa = '';
    let properties = '';
    let propertiesUa = '';

    if (divElement) {
        content = await page.evaluate(element => element.innerHTML, divElement);
        properties = await page.evaluate(element => element.innerHTML, divElementProp);

        await page.goto(urlUa);
        const divElementUa = await page.$(divSelector);
        const divElementPropUa = await page.$(divProperties);

        if (divElementUa) {
            contentUa = await page.evaluate(element => element.innerHTML, divElementUa);
            propertiesUa = await page.evaluate(element => element.innerHTML, divElementPropUa);
            console.log('properties = ' + propertiesUa);
        }

        await browser.close();
        return [content, contentUa, properties, propertiesUa];
    } else {
        console.log('Div not found.');
        await browser.close();
        return ['-', '-', '-', '-'];;
    }
}

async function main() {
    await getUrl();
}

main().then(r => console.log('Done'));
