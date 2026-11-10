# backend-aspnet — Porción vertical del Hito 2

Implementación en ASP.NET Core 8 del contrato común definido en
`docs/contrato-comparativo.md`, para el estudio comparativo de frameworks
(Componente D, IF0009).

## Estructura

```
backend-aspnet/
├── GuateGanado.Api/
│   ├── Controllers/     AuthController, AnimalController (6 endpoints)
│   ├── Data/            AppDbContext, SeedData (datos del contrato)
│   ├── Dtos/            Validación por DataAnnotations → 422 por campo
│   ├── Models/          Animal (7 campos) + Potrero (la relación) + Usuario
│   ├── Services/        TokenService (JWT), PasswordHasher (PBKDF2)
│   └── Program.cs       Auth, Swagger y override del 400 → 422
├── GuateGanado.Tests/   Las 3 pruebas equivalentes (xUnit)
└── docs/hito2-aspnet.md Métricas y procedimiento de medición
```

## Arranque rápido

```bash
dotnet new sln -n GuateGanado
dotnet sln add GuateGanado.Api/GuateGanado.Api.csproj
dotnet sln add GuateGanado.Tests/GuateGanado.Tests.csproj
dotnet restore
dotnet run --project GuateGanado.Api
```

Swagger en `/api/docs`. Pruebas con `dotnet test`.
Credenciales: `admin@guateganado.cr` / `Secret123`.

Ver `docs/hito2-aspnet.md` para las métricas y cómo completarlas.
