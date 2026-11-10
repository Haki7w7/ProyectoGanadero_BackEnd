using GuateGanado.Api.Models;
using GuateGanado.Api.Services;

namespace GuateGanado.Api.Data;

/// <summary>
/// Datos semilla identicos a los del contrato comparativo (docs/contrato-comparativo.md).
/// Las tres implementaciones deben partir exactamente de estos registros.
/// </summary>
public static class SeedData
{
    public static void Initialize(AppDbContext db)
    {
        db.Database.EnsureCreated();

        if (!db.Potreros.Any())
        {
            db.Potreros.Add(new Potrero { Id = 1, Nombre = "Potrero Norte" });
            db.Potreros.Add(new Potrero { Id = 2, Nombre = "Potrero Sur" });
            db.SaveChanges();
        }

        if (!db.Usuarios.Any())
        {
            db.Usuarios.Add(new Usuario
            {
                Email = "admin@guateganado.cr",
                PasswordHash = PasswordHasher.Hash("Secret123"),
                Rol = "administrador"
            });
            db.Usuarios.Add(new Usuario
            {
                Email = "operario@guateganado.cr",
                PasswordHash = PasswordHasher.Hash("Secret123"),
                Rol = "operario"
            });
            db.SaveChanges();
        }

        if (!db.Animales.Any())
        {
            db.Animales.AddRange(
                new Animal
                {
                    NumeroArete = "CR-1020-LIB",
                    Raza = "Brahman",
                    Sexo = "Hembra",
                    FechaNacimiento = new DateOnly(2023, 5, 15),
                    Estado = "Activo",
                    PotreroId = 1
                },
                new Animal
                {
                    NumeroArete = "CR-1021-LIB",
                    Raza = "Nelore",
                    Sexo = "Macho",
                    FechaNacimiento = new DateOnly(2022, 11, 20),
                    Estado = "Activo",
                    PotreroId = 1
                });
            db.SaveChanges();
        }
    }
}
