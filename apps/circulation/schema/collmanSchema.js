
schemaObj =
{
  title: "Collection management information API",
  type: "object",
  properties: {
    code: {
      type: "string",
      title: "OCLC Number OR Barcode"
    },
    acc: {
      type: "string",
      format: "radio",
      title: "Format",
      enum: ["atom_xml", "atom_json", "xml", "json"]
    }
  }
}
