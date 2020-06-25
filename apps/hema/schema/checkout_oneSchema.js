
schemaObj =
{
  title: "Check out",
  type: "object",
  properties: {
    user_barcode: {
      type: "string",
      minLength: 1,
      title: "Barcode lenerskaart"
    },
    item_barcode: {
      type: "string",
      minLength: 1,
      title: "Barcode item"
    }
  }
}
