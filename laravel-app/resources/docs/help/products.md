# Products

Use **Product** when you add catalogue items, print barcodes, or count warehouse stock. Digital products, services, and combos are included in search and barcode printing — not only physical stock.

![Product list](/public/help/screenshots/03-products-list.png)

## Open the product list

1. Sidebar → **Product** → **Product List**.
2. Search by name or code in the table search box.
3. Use **Action** on a row to view, edit, or manage the product.

## Add a product

![Add product](/public/help/screenshots/04-products-create.png)

1. Sidebar → **Product** → **Add Product**.
2. Choose the **Type**:
   - **Standard** — physical stock tracked in a warehouse
   - **Digital** — files / licences (no warehouse qty)
   - **Service** — labour or fees
   - **Combo** — a bundle of other products
3. Fill **Name**, **Code** (barcode), **Category**, and **Price**.
4. For standard items, set warehouse quantity if you already have stock.
5. Click **Submit**.

> Tip: Leave barcode symbology as **CODE128** unless you already print EAN/UPC labels. Alphanumeric codes work best with CODE128.

## Print barcodes

![Print barcode](/public/help/screenshots/05-print-barcode.png)

1. Sidebar → **Product** → **Print Barcode**.
2. In the product search box, type a **name** or **barcode**, or scan with a USB scanner and press Enter.
3. The product is added to the list. Adjust quantity of labels if needed.
4. Choose paper size / how many labels, then **Print**.

Scanner tip: click in the product field first, then scan. The scanner “types” the code and presses Enter; Ogera adds the line automatically.

## Stock count

![Stock count](/public/help/screenshots/06-stock-count.png)

1. Sidebar → **Product** → **Stock Count**.
2. Choose a **warehouse** and start a count.
3. Download / open the count sheet, fill **Counted** quantities, then finalise.
4. If quantities differ, use the adjustment step to update stock.

If you see an error when starting a count, try again after a refresh — the system needs a writable `stock_count` folder (this is fixed on current builds).

## Categories

Sidebar → **Product** → **Category** to create folders for the catalogue (e.g. Cameras, Lighting). Assign a category when you create or edit a product.
