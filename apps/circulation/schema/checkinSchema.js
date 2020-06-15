
schemaObj =
{
  title: "Check in",
  type: "object",
  properties: {
    item_barcode: {
      type: "array",
      title: "Barcodes",
      items: {
        type: "string",
        title: "Item"
      },
      default: [""]
    }
  }
}
