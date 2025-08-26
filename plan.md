# **XAO-PHP v2 — Project Plan**

## **Vision**

Rebuild **XAO-PHP** (XML Application Objects for PHP) into a **modern application framework** where:

* **XML DOM** is the canonical state tree for server-side and client-side interoperability.  
* React front-ends consume state as **JSON component trees** derived from XML.  
* Legacy **XSLT transformations** remain supported (server or client-side).  
* Extendability, performance, and developer ergonomics are prioritized.

---

## **Objectives**

1. **Modernize core framework** to PHP 8.x standards.  
2. **Streamline XML DOM pipeline** for content aggregation.  
3. **Equal rendering paths** (all first-class citizens):  
* **Client-side XSLT** → Browser/Electron theming with linked stylesheets  
* **HTMX-enhanced HTML** → Lightweight interactivity via server-rendered fragments  
* **JSON/REST API** → Canonical XML → JSON for React or other SPAs/mobile  
1. **Component registry system** (for JSON/React): Map XML elements to React components.  
2. **Developer-friendly APIs**: Abstract out DOM complexity for app developers.  
3. **Pluggable renderers**: Enable future renderers (e.g. GraphQL, PDF) without touching core.

---

## **Architecture Overview**

### **1\. Data Flow**

1. **Request Handling**  
* Router maps HTTP request to controller.  
* Controller builds canonical XML state tree.  
1. **Rendering Options**  
* **Path A (Legacy)**: Apply XSLT → HTML/XHTML → Browser.  
* **Path B (Modern)**: Convert XML → JSON graph → React front-end.  
* **Path C (Hybrid)**: Deliver raw XML \+ client-side XSLT (browser/Electron).

### **2\. Canonical State Tree (XML DOM)**

* Built using PHP DOM functions.  
* Represents *logical content \+ attributes*.  
* Independent of rendering format.

### **3\. React Consumption**

* XML → JSON transformation.

JSON format:  
{  
  "type": "ComponentName",  
  "props": { "id": 42, "title": "Hello" },  
  "children": \[ ... \]

* }

---

## **Deliverables & Milestones**

### **Phase 1 — Modernization**

* \[ \] Refactor core framework to PHP 8.x.  
* \[ \] Replace deprecated DOM APIs with `DOMDocument`, `DOMElement`, `DOMXPath`.  
* \[ \] Add namespaces, type hints, exception handling.  
* \[ \] Write PHPUnit tests for DOM building utilities.

### **Phase 2 — Rendering Abstraction**

* [x] Create `RendererInterface`.
  * [x] Implement `XsltRenderer` (client-side ready: serve XML + stylesheet ref).
  * [x] Implement `HtmxRenderer` (generate HTML fragments for HTMX swaps).
  * [x] Implement `JsonRenderer` (XML → JSON for API/React use).
* [x] Set up mechanism to choose rendering strategy via config/env or route param.

### **Phase 3 — React Integration**

* \[ \] Define a **component registry config file**: maps XML elements → React components.  
* \[ \] Provide helper functions to serialize XML into React-friendly JSON structure.  
* \[ \] Demo app: A simple blog with React frontend consuming JSON.

### **Phase 4 — Developer Ergonomics**

\[ \] Fluent builder API for XML assembly:  
$page \= $dom-\>create("page")  
            \-\>append("header", \["title" \=\> "Welcome"\])

*             \-\>append("article", \["id" \=\> 42\], "Hello World");  
* \[ \] Introduce middleware for injecting reusable XML fragments.  
* \[ \] Documentation & tutorials.

### **Phase 5 — Optional Enhancements**

* \[ \] Client-side XSLT demo (browser).  
* \[ \] Electron-based rendering.  
* \[ \] Benchmark: XML-to-JSON vs. direct DB-to-JSON approaches.

---

## **Risks & Considerations**

* **Browser XSLT support**: inconsistent; viable mostly for Electron apps.  
* **Performance overhead**: XML → JSON transformations should be optimized.  
* **Developer adoption**: JSON \> XML; abstract XML from day-to-day dev workflow.  
* **Compatibility**: Ensure DOM API use aligns with future PHP versions.

---

## **Long-term Opportunities**

* Plugin ecosystem: third-party XML → JSON transformers.  
* Headless CMS use case: XAO-PHP v2 as backend for multiple front-ends.  
* GraphQL bridge: auto-expose XML nodes as GraphQL types.  
* Multi-output rendering: XML available for AI/LLM agents to consume directly.

---

## **Next Steps**

1. **Initialize repo**: `xao-php-v2`.  
2. **Bootstrap just enough framework** to build XML state trees with PHP 8\.  
3. **Implement JSON renderer** → connect to simple React proof-of-concept.  
4. **Iterate forward** with registry \+ ergonomics.

