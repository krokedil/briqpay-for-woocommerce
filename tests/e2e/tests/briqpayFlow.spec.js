import puppeteer from "puppeteer";

describe("Briqpay test", () => {
	beforeAll(async () => {
		browser = await puppeteer.launch(options);
		context = await browser.createIncognitoBrowserContext();
		page = await context.newPage();

		await page.goto('https://google.com');


	}, 250000);

	afterAll(() => {
		if (!page.isClosed()) {
			browser.close();
			context.close();
		}
	}, 900000);

	test("First flow should be on google.com", async () => {
		const title = await page.title();
		expect(title).toEqual('Google');

	}, 190000);
});
