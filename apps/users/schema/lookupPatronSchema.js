
schemaObj =
{
  title: "Patron information by ppid or barcode",
  description: "In a real application the ppid (and therefore the barcode) is known because the user must be logged in.",
  type: "object",
  properties: {
    code: {
      type: "string",
      title: "Code (ppid or barcode)"
    }
  }
}
