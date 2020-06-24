/*
* Schema for registration and account form
*/

schemaObj =
{
  title: " ",
  type: "object",
  properties: {
    firstName: {
      type: "string",
      title: "First name"
    },
    lastName: {
      type: "string",
      title: "Last name *",
      minLength: 1
    },
    dateBirth: {
      type: "string",
      input_width:"40px",
      title: "Date of birth: (yyyy-mm-dd)",
      format: "datebirth"
    },
    gender: {
      type: "select",
      title: "Gender",
      enum: ["","male","female"]
    },
    address1: {
      type: "string",
      title: "Address *",
      minLength: 1
    },
    postcode: {
      type: "string",
      title: "Postal code *",
      minLength: 1
    },
    city: {
      type: "string",
      title: "City *",
      minLength: 1
    },
    state: {
      type: "string",
      title: "State/Province"
    },
    country: {
      type: "select",
      title: "Country *",
      enum: countryList
    },
    tel: {
      type: "string",
      format:"tel",
      title: "Telephone (mobile preferred)"
    },
    email: {
      type: "string",
      format:"email",
      title: "Email *",
      minLength: 1
    },
    userName: {
      type: "string",
      title: "User name",
      /* username MUST be equal to the provided email address */
      watch: {
        eml:"email"
      },
      template: "{{eml}}"
    },
    password: {
      type: "string",
      format: "password",
      title: "Password",
      description: "OCLC restrictions: <br/>Your password will be case-sensitive, and must be nine characters or more, with at least one non-alphabetic character. The characters semicolon (;), colon (:), apostrophe ('), and period (.) are not allowed."
    },
    instType: {
      type: "string",
      title: "Type of institution"
    },
    instName: {
      type: "string",
      title: "Institution or company name"
    },
    instAbbrev: {
      type: "string",
      title: "Institution or company abbreviation"
    },
    role: {
      type: "string",
      title: "Role"
    }
  }
};
