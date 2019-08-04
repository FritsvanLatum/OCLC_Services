/*
* Schema for registration and account form
*/

regSchemaObj =
{
  title: " ",
  type: "object",
  properties: {
    person: {
      type: "object",
      title: "Personal Information",
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
        email: {
          type: "string",
          format:"email",
          title: "Email *",
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
        tel: {
          type: "string",
          format:"tel",
          title: "Telephone (mobile preferred)"
        }
      }
    },
    id: {
      type: "object",
      title: "User id and password",
      properties: {
        userName: {
          type: "string",
          title: "User name",
          /* username MUST be equal to the provided email address */
          watch: {
            eml:"person.email"
          },
          template: "{{eml}}"
        },
        password: {
          id: "password",
          type: "string",
          format: "password",
          title: "Password",
          description: "Your password will be case-sensitive, and must be nine characters or more, with at least one non-alphabetic character. The characters semicolon (;), colon (:), apostrophe ('), and period (.) are not allowed."
        },
        confpw: {
          id: "confpw",
          type: "string",
          format: "password",
          title: "Confirm your password"
        }
      }
    },
    inst: {
      type: "object",
      title: "Institution",
      properties: {
        instType: {
          type: "select",
          title: "Type of institution",
          enum: [
          "",
          "court",
          "embassy",
          "university",
          "other"
          ]
        },
        instName: {
          type: "string",
          title: "Institution or company name"
        },
        research: {
          type: "string",
          title: "Research project",
          description: "If applicable, please share a short description or your research project."
        }
      }
    },
    address: {
      type: "object",
      title: "Address",
      properties: {
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
        }
      }
    },
    services: {
      id: "services",
      type: "object",
      title: "Library services",
      properties: {
        membership: {
          type: "select",
          title: "Do you want to become a library member?",
          enum: ["","Yes","No"]
        },
        membershipPeriod: {
          type: "select",
          title: "For which period?",
          options: {
            enum_titles: ["For one year","For a few days"]
          },
          enum: ["year","week"] 
        },
/*
receiveMails en receiveNews uitgezet - zie ook regValidators.js voor validatie op: 1 van de services moet op Yes staan
        receiveMails:{
          type: "select",
          title: "Do you want to receive mails send by the library?",
          enum: ["","Yes","No"]
        },
        receiveNews:{
          type: "select",
          title: "Do you want to receive the newsletter of the library?",
          enum: ["","Yes","No"]
        },*/
        receiveAlerts: {
          id: "receiveAlerts",
          type: "select",
          title: "Do you want to receive alerts from the library?",
          enum: ["","Yes","No"]
        },
        alertSubjects: {
          type: "object",
          title: "Alert subjects.",
          properties: {
            pubIntLaw: {
              type: "array",
              format: "checkbox",
              uniqueItems: true,
              items: {
                type: "string",
                enum:subjectsPubIntLaw
              },
              title: "Public internationa law."
            },
            privIntLaw: {
              type: "array",
              format: "checkbox",
              uniqueItems: true,
              items: {
                type: "string",
                enum:subjectsPrivIntLaw
              },
              title: "Private internationa law."
            },
            munCompLaw: {
              type: "array",
              format: "checkbox",
              uniqueItems: true,
              items: {
                type: "string",
                enum:subjectsMunCompLaw
              },
              title: "Municipal law and comparative law."
            },
            other: {
              type: "array",
              format: "checkbox",
              uniqueItems: true,
              items: {
                type: "string",
                enum:subjectsOther
              },
              title: "Other subjects."
            },
            special: {
              type: "array",
              format: "checkbox",
              uniqueItems: true,
              items: {
                type: "string",
                enum:subjectsSpecial
              },
              title: "Special subjects."
            }
          }
        }
      }
    }
  }
};
