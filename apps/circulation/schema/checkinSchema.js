
schemaObj =
{
  title: "Check in",
  type: "object",
  properties: {
    item_barcode: {
      type: "array",
      format: "table",
      title: "Barcode item",
      items: {
        type: "string",
        title: "Item"
      },
      default: [""]
    }
  }
}
