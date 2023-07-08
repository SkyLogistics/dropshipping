const puppeteer = require('puppeteer');
const fs = require('fs');
require('dotenv').config();

const folderPath = '/tmp'; // Replace with the path to the folder containing the files you want to delete
const path = require('path');
const substring = 'kartiny-po-nomeram'; // Replace with the desired substring

fs.readdir(folderPath, (err, files) => {
    if (err) {
        console.error('Error reading folder:', err);
        return;
    }

    files.forEach(file => {
        const filePath = path.join(folderPath, file);

        if (file.includes(substring)) {
            fs.unlink(filePath, err => {
                if (err) {
                    console.error(`Error deleting file ${filePath}:`, err);
                } else {
                    console.log(`File ${filePath} deleted successfully.`);
                }
            });
        }
    });
});

async function loginAndDownload() {
    const browser = await puppeteer.launch({
            headless: true,
            downloadsPath: '/tmp'
        }
    );

    const page = await browser.newPage();
    await page._client.send('Page.setDownloadBehavior', {
        behavior: 'allow',
        downloadPath: '/tmp',
    });
    await page.goto('https://royaltoys.com.ua/login/');
    await page.type('input[name="login"]', process.env.ROYAL_MAIL);
    await page.type('input[name="password"]', process.env.ROYAL_PASS);

    await page.evaluate(() => {
        const loginButton = [...document.querySelectorAll('input[type="submit"]')].find(el =>
            el.value.toLowerCase().includes('войти')
        );
        loginButton.click();
    });

    await page.waitForNavigation();
    const fileURL = 'https://royaltoys.com.ua/mprices/download/108/'; // Replace with the actual file URL

    try {
        await page.goto(fileURL);
        console.log('File downloaded successfully.');
    } catch (error) {
        await page.waitForTimeout(15000);
        await browser.close();
    }
    console.log('File downloaded successfully.');
}

loginAndDownload().catch(console.error);
