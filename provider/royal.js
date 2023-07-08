const puppeteer = require('puppeteer');
require('dotenv').config(); // Load environment variables from .env file

async function loginAndDownload() {
    const browser = await puppeteer.launch({headless: false});
    const page = await browser.newPage();

    // Navigate to the login page
    await page.goto('https://royaltoys.com.ua/login/');

    // Fill in the login form
    await page.type('input[name="login"]', process.env.ROYAL_MAIL);
    await page.type('input[name="password"]', process.env.ROYAL_PASS);

    await page.evaluate(() => {
        const loginButton = [...document.querySelectorAll('input[type="submit"]')].find(el =>
            el.value.toLowerCase().includes('войти')
        );
        loginButton.click();
    });

    // Wait for navigation to complete after login
    await page.waitForNavigation();

    // Navigate to the page with the download link
    await page.goto('https://royaltoys.com.ua/mprices/', {
        waitUntil: 'networkidle0' // Wait until there are no more than 0 network connections for at least 500 ms
    });

    await page.goto(' https://royaltoys.com.ua/mprices/14');

    await page.waitForTimeout(5000);


    async function downloadFileFromURL(url, filePath) {
        const browser = await puppeteer.launch();
        const page = await browser.newPage();

        // Enable downloads in headless mode
        await page._client.send('Page.setDownloadBehavior', {
            behavior: 'allow',
            downloadPath: filePath
        });

        // Navigate to the URL
        await page.goto(url);

        // Wait for the download to complete
        await page.waitForTimeout(5000);

        console.log('File downloaded successfully.');

        // Close the browser
        await browser.close();
        // Close the browser
        await browser.close();
    }

    // Usage
    const fileURL = 'https://royaltoys.com.ua/mprices/download/108/'; // Replace with the actual file URL
    const filePath = './downloaded-file.xlsx'; // Replace with the desired file path

    downloadFileFromURL(fileURL, filePath)
        .then(() => console.log('File downloaded successfully.'))
        .catch(error => console.error('Error downloading file:', error));

    await page.waitForTimeout(10000);

    // // Get the download link element
    // const downloadLink = await page.$('#download-link');
    //
    // // Trigger the download by clicking the link
    // await downloadLink.click();
    //
    // // Wait for the download to start
    // const downloadRequest = await page.waitForResponse(response => {
    //     return response.request().resourceType() === 'document' && response.url().endsWith('.xls');
    // });
    //
    // // Get the downloaded file buffer
    // const fileBuffer = await downloadRequest.buffer();
    //
    // // Save the file to local storage
    // const filePath = './downloaded-file.xls'; // Specify the path where the file should be saved
    // require('fs').writeFileSync(filePath, fileBuffer);

    console.log('File downloaded successfully.');

    // Close the browser
    await browser.close();
}

loginAndDownload().catch(console.error);
